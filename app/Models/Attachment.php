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

    // Type constants
    const TYPE_ATTACHMENT = 'attachment';
    const TYPE_DIGITAL_FOLDER = 'digital_folder';
    const TYPE_DIGITAL_DOCUMENT = 'digital_document';
    const TYPE_ARTIFACT = 'artifact';
    const TYPE_BOOK = 'book';
    const TYPE_PERIODIC = 'periodic';

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
        // New Phase 1 fields
        'ocr_language',
        'ocr_confidence',
        'file_encoding',
        'page_count',
        'word_count',
        'file_hash_md5',
        'file_extension',
        'is_primary',
        'display_order',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
        'ocr_confidence' => 'float',
        'page_count' => 'integer',
        'word_count' => 'integer',
        'is_primary' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];




    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_attachment', 'attachment_id', 'task_id');
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get full file path
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get primary attachments
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrderedByDisplay($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at');
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
