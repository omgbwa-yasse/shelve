<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusConceptProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept_id',
        'property_name',
        'property_value',
        'language',
    ];

    /**
     * Relation avec le concept parent
     */
    public function concept(): BelongsTo
    {
        return $this->belongsTo(ThesaurusConcept::class, 'concept_id');
    }

    /**
     * Scope pour filtrer par nom de propriété
     */
    public function scopeByProperty($query, $propertyName)
    {
        return $query->where('property_name', $propertyName);
    }

    /**
     * Scope pour filtrer par langue
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope pour les propriétés Dublin Core
     */
    public function scopeDublinCore($query)
    {
        return $query->where('property_name', 'LIKE', 'dc:%');
    }

    /**
     * Scope pour les propriétés personnalisées
     */
    public function scopeCustom($query)
    {
        return $query->where('property_name', 'NOT LIKE', 'dc:%');
    }
}
