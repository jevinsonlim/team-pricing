<?php

namespace App\Models;

use App\UploadProcessStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TeamPartUpload extends Model
{
    use HasFactory;

    protected $hidden = ['upload_file', 'remarks_file'];

    protected $appends = ['is_successful', 'process_status', 'upload_file_url', 'remarks_file_url'];

    protected function casts(): array
    {
        return [
            'process_began_at' => 'datetime',
            'process_ended_at' => 'datetime'
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function isSuccessful(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['process_ended_at'] && !$attributes['remarks_file']) return true;

                return false;
            },
        );
    }

    protected function processStatus(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['process_ended_at']) return UploadProcessStatus::Processed;

                if ($attributes['process_began_at']) return UploadProcessStatus::Processing;

                return UploadProcessStatus::Pending;
            },
        );
    }

    protected function uploadFileUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return Storage::url($attributes['upload_file']);
            },
        );
    }

    protected function remarksFileUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!$attributes['remarks_file']) return null;

                return Storage::url($attributes['remarks_file']);
            },
        );
    }
}
