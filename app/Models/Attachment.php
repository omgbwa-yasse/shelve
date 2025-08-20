<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\ExtractAttachmentText;

class Attachment extends Model
{
    use HasFactory;
    use Searchable;


    protected $table = 'attachments';


    protected $fillable = [
        'path',
        'name',
        'crypt',
        'crypt_sha512',
        'size',
        'creator_id',
        'type',
        'thumbnail_path',
        'mime_type',
    'content_text',
    ];




    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_attachment', 'attachment_id', 'task_id');
    }

    protected static function booted()
    {
        static::created(function (Attachment $attachment) {
            // Extract and index content asynchronously (respects queue driver)
            ExtractAttachmentText::dispatch($attachment->id)->onQueue('default');
        });

        static::updated(function (Attachment $attachment) {
            // If file path or mime type changed, re-extract
            if ($attachment->wasChanged(['path', 'mime_type', 'name'])) {
                ExtractAttachmentText::dispatch($attachment->id)->onQueue('default');
            }
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'type' => $this->type,
            'content' => $this->content_text, // main fulltext field
        ];
    }

}
