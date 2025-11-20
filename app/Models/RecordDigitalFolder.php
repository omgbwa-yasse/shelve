<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class RecordDigitalFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type_id',
        'parent_id',
        'metadata',
        'access_level',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'approval_notes',
        'creator_id',
        'organisation_id',
        'assigned_to',
        'documents_count',
        'subfolders_count',
        'total_size',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'metadata' => 'array',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'documents_count' => 'integer',
        'subfolders_count' => 'integer',
        'total_size' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relations
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalFolderType::class, 'type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RecordDigitalFolder::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RecordDigitalDocument::class, 'folder_id');
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'record_digital_folder_keyword', 'folder_id', 'keyword_id');
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_digital_folders', 'folder_id', 'dolly_id')
            ->withTimestamps();
    }

    public function thesaurusConcepts()
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'record_digital_folder_thesaurus_concept',
            'folder_id',
            'concept_id'
        )
        ->withPivot('weight', 'context', 'extraction_note')
        ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOfType($query, $typeCode)
    {
        return $query->whereHas('type', function ($q) use ($typeCode) {
            $q->where('code', $typeCode);
        });
    }

    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    /**
     * Tree navigation methods
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function getRoot(): ?RecordDigitalFolder
    {
        if ($this->isRoot()) {
            return $this;
        }

        return $this->parent->getRoot();
    }

    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    public function getDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    public function getSiblings(): Collection
    {
        if ($this->isRoot()) {
            return static::roots()->where('id', '!=', $this->id)->get();
        }

        return $this->parent->children()->where('id', '!=', $this->id)->get();
    }

    public function getPath(string $separator = ' / '): string
    {
        $path = collect([$this->name]);

        $ancestors = $this->getAncestors();
        foreach ($ancestors->reverse() as $ancestor) {
            $path->prepend($ancestor->name);
        }

        return $path->implode($separator);
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
     * Business logic methods
     */
    public function canAddDocument(RecordDigitalDocumentType $documentType): bool
    {
        return $this->type->isDocumentTypeAllowed($documentType->code);
    }

    public function approve(User $user, ?string $notes = null): void
    {
        $this->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function updateStatistics(): void
    {
        $this->documents_count = $this->documents()->count();
        $this->subfolders_count = $this->children()->count();
        // Note: total_size calculation commented out because record_digital_documents
        // table doesn't have total_size column yet (will be added in Phase 5)
        // $this->total_size = $this->documents()->sum('file_size')
        //     + $this->children()->sum('total_size');
        $this->total_size = $this->children()->sum('total_size');
        $this->save();

        // Propagate to parent
        if ($this->parent) {
            $this->parent->updateStatistics();
        }
    }

    /**
     * Accessors
     */
    public function getTotalSizeHumanAttribute(): string
    {
        return $this->formatBytes($this->total_size);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
