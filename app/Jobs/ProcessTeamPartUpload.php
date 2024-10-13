<?php

namespace App\Jobs;

use App\Models\TeamPart;
use App\Models\TeamPartUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Str;
use League\Csv\Writer;

class ProcessTeamPartUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public TeamPartUpload $teamPartUpload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->teamPartUpload->process_began_at = now();
            $this->teamPartUpload->save();

            $uploadFileCsv = Reader::createFromPath(
                Storage::path($this->teamPartUpload->upload_file),
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
                        'Manufacturer' => 'required',
                        'Model Number' => 'required',
                        'Multiplier' => 'numeric|gt:0|required_without:Static Price',
                        'Static Price' => 'numeric|gt:0|required_without:Multiplier',
                    ]
                );

                $manualValidationErrors = [];

                $teamPartToUpdate = TeamPart::with('part')
                    ->where('team_id', $this->teamPartUpload->team_id)
                    ->whereRelation('part', 'manufacturer', $record['Manufacturer'])
                    ->whereRelation('part', 'model_number', $record['Model Number'])
                    ->first();

                if (!$teamPartToUpdate) {
                    $manualValidationErrors[] = "Manufacturer & Model Number pair does not exist on team parts.";
                }

                if ($teamPartToUpdate && !$teamPartToUpdate->part->is_active) {
                    $manualValidationErrors[] = "Inactive part.";
                }

                if ($validator->fails() || $manualValidationErrors) {
                    if ($withErrorsCount === 0) {
                        $remarksFileCsv = Writer::createFromPath(
                            Storage::path($remarksFile),
                            'a'
                        );

                        $header[] = 'Remarks';
                        $remarksFileCsv->insertOne($header);
                    }

                    $errors = implode(PHP_EOL, $validator->errors()->all() + $manualValidationErrors);
                    $record['Remarks'] = $errors;
                    $remarksFileCsv->insertOne($record);

                    $withErrorsCount++;
                    continue;
                }

                $data = $validator->validated();

                if (
                    $data['Multiplier'] && $data['Static Price'] ||
                    $data['Multiplier']
                ) {
                    $teamPartToUpdate->multiplier = $data['Multiplier'];
                    $teamPartToUpdate->static_price = null;
                    $teamPartToUpdate->team_price = $teamPartToUpdate->part->list_price
                        * $data['Multiplier'];
                } else {
                    $teamPartToUpdate->multiplier = null;
                    $teamPartToUpdate->static_price = $data['Static Price'];
                    $teamPartToUpdate->team_price = $data['Static Price'];
                }

                $teamPartToUpdate->save();
            }

            if ($withErrorsCount > 0) {
                $this->teamPartUpload->remarks_file = $remarksFile;
                $this->teamPartUpload->error_message = "{$withErrorsCount} record(s) were not uploaded.";
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $this->teamPartUpload->error_message = 'A system error has occured';
        } finally {
            $this->teamPartUpload->process_ended_at = now();
            $this->teamPartUpload->save();
        }
    }
}
