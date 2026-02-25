<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\ExtractAttachmentText;
use Illuminate\Support\Facades\Auth;

class Attachment extends Model
{
    use HasFactory;
    use Searchable;

    // Type constants
    const TYPE_ATTACHMENT = 'attachment';
    const TYPE_DIGITAL_FOLDER = 'digital_folder';
    const TYPE_DIGITAL_DOCUMENT = 'digital_document';
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

    /**
     * Create an attachment from an uploaded file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $type Type of attachment (use class constants)
     * @param int|null $creatorId
     * @param array $additionalData Additional fillable data
     * @return self
     */
    public static function createFromUpload($file, string $type = self::TYPE_ATTACHMENT, ?int $creatorId = null, array $additionalData = []): self
    {
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $hash = hash('sha256', $originalName . time() . uniqid());
        $filename = $nameWithoutExt . '_' . substr($hash, 0, 16) . '.' . $extension;

        // Store file in appropriate directory based on type
        $directory = match($type) {
            self::TYPE_DIGITAL_DOCUMENT => 'digital_documents',
            self::TYPE_DIGITAL_FOLDER => 'digital_folders',
            self::TYPE_BOOK => 'books',
            self::TYPE_PERIODIC => 'periodics',
            default => 'attachments',
        };

        $path = $file->storeAs($directory, $filename, 'local');

        // Calculate hashes
        $filePath = storage_path('app/' . $path);
        $md5Hash = md5_file($filePath);
        $sha512Hash = hash_file('sha512', $filePath);

        // Create attachment record
        return self::create(array_merge([
            'path' => $path,
            'name' => $originalName,
            'crypt' => $hash,
            'crypt_sha512' => $sha512Hash,
            'size' => $file->getSize(),
            'creator_id' => $creatorId ?? Auth::id(),
            'type' => $type,
            'mime_type' => $file->getMimeType(),
            'file_hash_md5' => $md5Hash,
            'file_extension' => $extension,
        ], $additionalData));
    }

    /**
     * Get download response for this attachment
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        return response()->download($this->full_path, $this->name, [
            'Content-Type' => $this->mime_type,
        ]);
    }

    /**
     * Get thumbnail URL with fallback to default icon
     */
    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail_path && Storage::disk('local')->exists($this->thumbnail_path)) {
            return asset('storage/' . $this->thumbnail_path);
        }

        // Fallback to default thumbnail based on MIME type or file extension
        return $this->getDefaultThumbnailUrl();
    }

    /**
     * Get default thumbnail icon based on file type
     */
    public function getDefaultThumbnailUrl(): string
    {
        $extension = strtolower($this->file_extension);
        $mimeType = $this->mime_type ?? '';

        $iconMap = [
            // Documents
            'pdf' => 'pdf',
            'doc' => 'word',
            'docx' => 'word',
            'xls' => 'excel',
            'xlsx' => 'excel',
            'ppt' => 'powerpoint',
            'pptx' => 'powerpoint',
            'txt' => 'document',

            // Images
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'gif' => 'image',
            'bmp' => 'image',
            'svg' => 'image',

            // Archives
            'zip' => 'archive',
            'rar' => 'archive',
            '7z' => 'archive',
            'tar' => 'archive',
            'gz' => 'archive',
        ];

        $icon = $iconMap[$extension] ?? 'document';

        // Try to find icon file in public/images/file-icons/
        $iconPath = "images/file-icons/{$icon}.svg";
        if (file_exists(public_path($iconPath))) {
            return asset($iconPath);
        }

        // Fallback to generic document icon
        return asset('images/file-icons/document.svg');
    }

    /**
     * Check if this attachment can generate a thumbnail
     */
    public function canGenerateThumbnail(): bool
    {
        // Check if imagick is available
        if (!extension_loaded('imagick')) {
            return false;
        }

        $mimeType = $this->mime_type ?? '';
        $extension = strtolower($this->file_extension);

        // PDF and images can generate thumbnails
        return strpos($mimeType, 'pdf') !== false ||
               strpos($mimeType, 'image/') === 0 ||
               in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp']);
    }

    protected static function booted()
    {
        static::created(function (Attachment $attachment) {
            // Extract and index content asynchronously (respects queue driver)
            ExtractAttachmentText::dispatch($attachment->id)->onQueue('default');

            // Generate thumbnail for digital documents if possible
            if ($attachment->type === self::TYPE_DIGITAL_DOCUMENT && $attachment->canGenerateThumbnail()) {
                \App\Jobs\GenerateDocumentThumbnail::dispatch($attachment)->onQueue('default');
            }
        });

        static::updated(function (Attachment $attachment) {
            // If file path or mime type changed, re-extract
            if ($attachment->wasChanged(['path', 'mime_type', 'name'])) {
                ExtractAttachmentText::dispatch($attachment->id)->onQueue('default');

                // Regenerate thumbnail if file changed
                if ($attachment->type === self::TYPE_DIGITAL_DOCUMENT && $attachment->canGenerateThumbnail()) {
                    \App\Jobs\GenerateDocumentThumbnail::dispatch($attachment)->onQueue('default');
                }
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
