<?php

namespace App\Jobs;

use App\Models\Part;
use App\Models\PartUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Csv\Writer;

class ProcessPartUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PartUpload $partUpload
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->partUpload->process_began_at = now();
            $this->partUpload->save();

            $uploadFileCsv = Reader::createFromPath(
                Storage::path($this->partUpload->upload_file), 
                'r'
            );
            $uploadFileCsv->setHeaderOffset(0);
            $header = $uploadFileCsv->getHeader();
            $records = $uploadFileCsv->getRecords();
            $withErrorsCount = 0;
            $remarksFile = Str::random(40) . '.csv';
            $remarksFileCsv = null;
            
            foreach ($records as $record) {
                $validator = Validator::make(
                    $record,
                    [
                        'Part Type' => 'required',
                        'Manufacturer' => 'required',
                        'Model Number' => 'required',
                        'List Price' => 'required|numeric|min:0',
                        'Active' => Rule::in(['Y', 'N'])
                    ]
                );

                if ($validator->fails()) {
                    if ($withErrorsCount === 0) {
                        $remarksFileCsv = Writer::createFromPath(
                            Storage::path($remarksFile),
                            'a'
                        );

                        $header[] = 'Remarks';
                        $remarksFileCsv->insertOne($header);
                    }

                    $errors = implode(PHP_EOL, $validator->errors()->all());
                    $record['Remarks'] = $errors;
                    $remarksFileCsv->insertOne($record);

                    $withErrorsCount++;
                    continue;
                }

                $data = $validator->validated();

                Part::upsert(
                    [
                        'part_type' => $data['Part Type'],
                        'manufacturer' => $data['Manufacturer'],
                        'model_number' => $data['Model Number'],
                        'list_price' => $data['List Price'],
                        'is_active' => $data['Active'] === 'Y',
                    ],
                    uniqueBy: ['manufacturer', 'model_number'],
                    update: ['part_type', 'list_price', 'is_active']
                );
            }

            if ($withErrorsCount > 0) {
                $this->partUpload->remarks_file = $remarksFile;
                $this->partUpload->error_message = "{$withErrorsCount} record(s) were not uploaded.";
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $this->partUpload->error_message = 'A system error has occured';
        } finally {
            $this->partUpload->process_ended_at = now();
            $this->partUpload->save();
        }
    }
}