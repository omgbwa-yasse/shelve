<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;

class RecordDigitalDocument extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganisation;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type_id',
        'folder_id',
        'attachment_id',
        'version_number',
        'is_current_version',
        'parent_version_id',
        'version_notes',
        'checked_out_by',
        'checked_out_at',
        'signature_status',
        'signed_by',
        'signed_at',
        'signature_data',
        'metadata',
        'access_level',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'approval_notes',
        'retention_until',
        'is_archived',
        'archived_at',
        'creator_id',
        'organisation_id',
        'assigned_to',
        'download_count',
        'last_viewed_at',
        'last_viewed_by',
        'document_date',
        'transferred_at',
        'transferred_to_record_id',
        'transfer_metadata',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_current_version' => 'boolean',
        'checked_out_at' => 'datetime',
        'signed_at' => 'datetime',
        'metadata' => 'array',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'retention_until' => 'date',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'download_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'document_date' => 'date',
        'transferred_at' => 'datetime',
        'transfer_metadata' => 'array',
    ];

    /**
     * Relations
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalDocumentType::class, 'type_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'folder_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalDocument::class, 'parent_version_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(RecordDigitalDocument::class, 'parent_version_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function transferredToRecord(): BelongsTo
    {
        return $this->belongsTo(RecordPhysical::class, 'transferred_to_record_id');
    }

    public function checkedOutUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lastViewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_viewed_by');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'record_digital_document_keyword', 'document_id', 'keyword_id');
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_digital_documents', 'document_id', 'dolly_id')
            ->withTimestamps();
    }

    public function thesaurusConcepts()
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'record_digital_document_thesaurus_concept',
            'document_id',
            'concept_id'
        )
        ->withPivot('weight', 'context', 'extraction_note')
        ->withTimestamps();
    }

    /**
     * Metadata methods
     */
    public function getMetadataValue(string $metadataCode, $default = null)
    {
        return $this->metadata[$metadataCode] ?? $default;
    }

    public function setMetadataValue(string $metadataCode, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$metadataCode] = $value;
        $this->metadata = $metadata;
    }

    public function setMultipleMetadata(array $metadataArray): void
    {
        $metadata = $this->metadata ?? [];
        foreach ($metadataArray as $code => $value) {
            $metadata[$code] = $value;
        }
        $this->metadata = $metadata;
    }

    public function getRequiredMetadataFields(): array
    {
        if (!$this->type) {
            return [];
        }

        return $this->type->getMandatoryMetadataDefinitions()
            ->map(function ($definition) {
                return [
                    'code' => $definition->code,
                    'name' => $definition->name,
                    'data_type' => $definition->data_type,
                    'required' => true,
                ];
            })
            ->toArray();
    }

    public function getVisibleMetadataFields(): array
    {
        if (!$this->type) {
            return [];
        }

        return $this->type->getVisibleMetadataDefinitions()
            ->map(function ($definition) {
                return [
                    'code' => $definition->code,
                    'name' => $definition->name,
                    'data_type' => $definition->data_type,
                    'value' => $this->getMetadataValue($definition->code),
                    'required' => $definition->pivot->mandatory,
                    'readonly' => $definition->pivot->readonly,
                ];
            })
            ->toArray();
    }

    public function validateMetadata(): array
    {
        if (!$this->type) {
            return ['type' => 'Document type not set'];
        }

        $service = app(\App\Services\MetadataValidationService::class);

        try {
            $service->validateDocumentMetadata($this->type_id, $this->metadata ?? []);
            return [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $e->errors();
        }
    }

    public function hasCompleteMetadata(): bool
    {
        return empty($this->validateMetadata());
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeCurrentVersions($query)
    {
        return $query->where('is_current_version', true);
    }

    public function scopeOfType($query, $typeCode)
    {
        return $query->whereHas('type', function ($q) use ($typeCode) {
            $q->where('code', $typeCode);
        });
    }

    public function scopeInFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeCheckedOut($query)
    {
        return $query->whereNotNull('checked_out_by');
    }

    public function scopeSigned($query)
    {
        return $query->where('signature_status', 'signed');
    }

    /**
     * Code generation using type's pattern
     */
    public function generateCode(): string
    {
        if (!$this->type) {
            throw new \Exception('Cannot generate code without a type');
        }

        return $this->type->generateCode();
    }

    /**
     * Versioning methods
     */
    public function createNewVersion(User $user, UploadedFile $file, ?string $notes = null): RecordDigitalDocument
    {
        if ($this->isCheckedOut()) {
            throw new \Exception('Document is currently checked out');
        }

        // Mark current version as not current
        $this->is_current_version = false;
        $this->save();

        // Get all versions to find the max version number
        $maxVersion = static::where('parent_version_id', $this->parent_version_id ?? $this->id)
            ->max('version_number');

        // Create new version
        $newVersion = $this->replicate();
        $newVersion->version_number = $maxVersion + 1;
        $newVersion->is_current_version = true;
        $newVersion->parent_version_id = $this->parent_version_id ?? $this->id;
        $newVersion->version_notes = $notes;
        $newVersion->creator_id = $user->id;
        $newVersion->status = 'draft';
        $newVersion->signature_status = 'unsigned';
        $newVersion->signed_by = null;
        $newVersion->signed_at = null;
        $newVersion->save();

        // Stocker le fichier uploadé comme attachment
        $attachment = Attachment::createFromUpload(
            $file,
            Attachment::TYPE_DIGITAL_DOCUMENT,
            $user->id,
            [
                'description' => $notes,
                'is_primary' => true,
            ]
        );
        $newVersion->attachment_id = $attachment->id;
        $newVersion->save();

        return $newVersion;
    }

    public function getLatestVersion(): RecordDigitalDocument
    {
        $rootId = $this->parent_version_id ?? $this->id;

        return static::where(function ($q) use ($rootId) {
            $q->where('id', $rootId)
              ->orWhere('parent_version_id', $rootId);
        })
        ->where('is_current_version', true)
        ->first() ?? $this;
    }

    public function getAllVersions(): Collection
    {
        $rootId = $this->parent_version_id ?? $this->id;

        return static::where(function ($q) use ($rootId) {
            $q->where('id', $rootId)
              ->orWhere('parent_version_id', $rootId);
        })
        ->orderBy('version_number', 'desc')
        ->get();
    }

    /**
     * Check-out / Check-in methods
     */
    public function checkout(User $user): void
    {
        if ($this->isCheckedOut()) {
            throw new \Exception('Document is already checked out');
        }

        if (!$this->is_current_version) {
            throw new \Exception('Only current version can be checked out');
        }

        $this->update([
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
        ]);
    }

    public function checkin(User $user, UploadedFile $file, ?string $notes = null): RecordDigitalDocument
    {
        if (!$this->isCheckedOut()) {
            throw new \Exception('Document is not checked out');
        }

        if ($this->checked_out_by !== $user->id) {
            throw new \Exception('Document is checked out by another user');
        }

        // Create new version
        $newVersion = $this->createNewVersion($user, $file, $notes);

        // Cancel checkout on current version
        $this->cancelCheckout($user);

        return $newVersion;
    }

    public function cancelCheckout(User $user): void
    {
        if (!$this->isCheckedOut()) {
            throw new \Exception('Document is not checked out');
        }

        if ($this->checked_out_by !== $user->id) {
            throw new \Exception('Document is checked out by another user');
        }

        $this->update([
            'checked_out_by' => null,
            'checked_out_at' => null,
        ]);
    }

    public function isCheckedOut(): bool
    {
        return !is_null($this->checked_out_by);
    }

    public function isCheckedOutBy(User $user): bool
    {
        return $this->checked_out_by === $user->id;
    }

    /**
     * Signature methods
     */
    public function sign(User $user, ?string $signatureData = null): void
    {
        if ($this->signature_status === 'signed') {
            throw new \Exception('Document is already signed');
        }

        if ($this->isCheckedOut()) {
            throw new \Exception('Cannot sign a checked out document');
        }

        $this->update([
            'signature_status' => 'signed',
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_data' => $signatureData,
        ]);
    }

    public function rejectSignature(User $user, ?string $reason = null): void
    {
        $this->update([
            'signature_status' => 'rejected',
            'approval_notes' => $reason,
        ]);
    }

    /**
     * Approval methods
     */
    public function approve(User $user, ?string $notes = null): void
    {
        $this->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'status' => 'active',
        ]);
    }

    /**
     * Archive methods
     */
    public function archive(): void
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'status' => 'archived',
        ]);
    }

    public function unarchive(): void
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Tracking methods
     */
    public function trackView(User $user): void
    {
        $this->increment('download_count');
        $this->update([
            'last_viewed_at' => now(),
            'last_viewed_by' => $user->id,
        ]);
    }

    /**
     * Validation method
     */
    public function validateFile(UploadedFile $file): array
    {
        if (!$this->type) {
            return ['type' => 'Document type not set'];
        }

        return $this->type->validateFile(
            $file->getMimeType(),
            $file->getClientOriginalExtension(),
            $file->getSize()
        );
    }

    /**
     * Accessors delegated to Attachment
     */
    public function getFileSizeHumanAttribute(): string
    {
        return $this->attachment ? $this->attachment->file_size_human : '0 B';
    }

    public function getFilePathAttribute(): ?string
    {
        return $this->attachment ? $this->attachment->path : null;
    }

    public function getExtensionAttribute(): string
    {
        return $this->attachment ? $this->attachment->file_extension : '';
    }
}
