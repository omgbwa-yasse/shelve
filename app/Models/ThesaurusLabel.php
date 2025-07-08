<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept_id',
        'uri',
        'label_type',
        'literal_form',
        'language',
        'status',
    ];

    /**
     * Relation avec le concept parent
     */
    public function concept(): BelongsTo
    {
        return $this->belongsTo(ThesaurusConcept::class, 'concept_id');
    }

    /**
     * Scope pour filtrer par type de label
     */
    public function scopeByType($query, $type)
    {
        return $query->where('label_type', $type);
    }

    /**
     * Scope pour filtrer par langue
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope pour les labels actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope pour les labels préférés
     */
    public function scopePreferred($query)
    {
        return $query->where('label_type', 'prefLabel');
    }

    /**
     * Scope pour les labels alternatifs
     */
    public function scopeAlternative($query)
    {
        return $query->where('label_type', 'altLabel');
    }

    /**
     * Scope pour les labels cachés
     */
    public function scopeHidden($query)
    {
        return $query->where('label_type', 'hiddenLabel');
    }
}
