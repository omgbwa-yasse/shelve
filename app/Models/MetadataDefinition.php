<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataDefinition extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'data_type',
        'validation_rules',
        'options',
        'reference_list_id',
        'searchable',
        'sortable',
        'highlightable',
        'autocomplete',
        'unique',
        'input_mask',
        'max_length',
        'copy_source_type',
        'copy_source_field',
        'computed_template',
        'active',
        'is_system',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'searchable' => 'boolean',
        'sortable' => 'boolean',
        'highlightable' => 'boolean',
        'autocomplete' => 'boolean',
        'unique' => 'boolean',
        'max_length' => 'integer',
        'active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope à n'inclure que les définitions système (ex. champs ISAD(G) communs à
     * tous les types) par opposition aux définitions personnalisées créées par type.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Get the metadata profiles for this definition.
     */
    public function metadataProfiles(): HasMany
    {
        return $this->hasMany(MetadataProfile::class);
    }

    /**
     * Get the document metadata records using this definition.
     */
    public function documentMetadata(): HasMany
    {
        return $this->hasMany(DocumentMetadata::class, 'metadata_definition_id');
    }

    /**
     * Get the folder metadata records using this definition.
     */
    public function folderMetadata(): HasMany
    {
        return $this->hasMany(FolderMetadata::class, 'metadata_definition_id');
    }

    /**
     * Get the reference list if this metadata uses one.
     */
    public function referenceList(): BelongsTo
    {
        return $this->belongsTo(ReferenceList::class);
    }

    /**
     * Get the user who created this definition.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this definition.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active definitions.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include searchable definitions.
     */
    public function scopeSearchable($query)
    {
        return $query->where('searchable', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
