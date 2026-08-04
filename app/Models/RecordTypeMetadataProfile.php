<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordTypeMetadataProfile extends Model
{
    use HasFactory;

    protected $table = 'record_type_metadata_profiles';

    protected $fillable = [
        'record_type_id',
        'metadata_definition_id',
        'mandatory',
        'visible',
        'readonly',
        'default_value',
        'validation_rules',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'visible' => 'boolean',
        'readonly' => 'boolean',
        'validation_rules' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Le type de notice auquel appartient ce profil.
     */
    public function recordType(): BelongsTo
    {
        return $this->belongsTo(RecordType::class, 'record_type_id');
    }

    /**
     * La définition de métadonnée de ce profil.
     */
    public function metadataDefinition(): BelongsTo
    {
        return $this->belongsTo(MetadataDefinition::class);
    }

    /**
     * Scope à n'inclure que les profils obligatoires.
     */
    public function scopeMandatory($query)
    {
        return $query->where('mandatory', true);
    }

    /**
     * Scope à n'inclure que les profils visibles.
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope pour trier par ordre d'affichage.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
