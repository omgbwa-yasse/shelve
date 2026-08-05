<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceList extends Model
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
        'active',
        'linked_schema_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Domaines de valeurs système livrés par le seeder par défaut.
     * Les codes éligibles au champ « Schéma lié » (étape 2 du plan).
     */
    public const DEFAULT_SYSTEM_CODES = [
        'CONTAINER_TYPES',
        'FOLDER_TYPES',
        'LOCATION_TYPES',
        'SUPPORT_TYPES',
        'TASK_TYPES',
        'TASK_STATUS',
        'YEAR_TYPES',
        'PRIORITY_TYPES',
    ];

    /**
     * Codes des domaines système éligibles à une liaison « Schéma lié ».
     */
    public const LINKED_SCHEMA_ELIGIBLE_CODES = [
        'CONTAINER_TYPES',
        'FOLDER_TYPES',
        'LOCATION_TYPES',
        'SUPPORT_TYPES',
        'TASK_TYPES',
    ];

    /**
     * Get the values for this reference list.
     */
    public function values(): HasMany
    {
        return $this->hasMany(ReferenceValue::class, 'list_id');
    }

    /**
     * Get the active values for this reference list.
     */
    public function activeValues(): HasMany
    {
        return $this->hasMany(ReferenceValue::class, 'list_id')
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('value');
    }

    /**
     * Get metadata definitions using this reference list.
     */
    public function metadataDefinitions(): HasMany
    {
        return $this->hasMany(MetadataDefinition::class);
    }

    /**
     * Le schéma (RecordType) associé à ce domaine de valeurs (étape 2).
     */
    public function linkedSchema(): BelongsTo
    {
        return $this->belongsTo(RecordType::class, 'linked_schema_id');
    }

    /**
     * Les domaines système éligibles au champ « Schéma lié ».
     */
    public function isLinkedSchemaEligible(): bool
    {
        return in_array($this->code, self::LINKED_SCHEMA_ELIGIBLE_CODES, true);
    }

    /**
     * Get the user who created this list.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this list.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active lists.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
