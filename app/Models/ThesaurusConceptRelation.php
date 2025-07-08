<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusConceptRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_concept_id',
        'target_concept_id',
        'relation_type',
    ];

    /**
     * Relation avec le concept source
     */
    public function sourceConcept(): BelongsTo
    {
        return $this->belongsTo(ThesaurusConcept::class, 'source_concept_id');
    }

    /**
     * Relation avec le concept cible
     */
    public function targetConcept(): BelongsTo
    {
        return $this->belongsTo(ThesaurusConcept::class, 'target_concept_id');
    }

    /**
     * Scope pour filtrer par type de relation
     */
    public function scopeByType($query, $type)
    {
        return $query->where('relation_type', $type);
    }

    /**
     * Scope pour les relations hiérarchiques
     */
    public function scopeHierarchical($query)
    {
        return $query->whereIn('relation_type', ['broader', 'narrower']);
    }

    /**
     * Scope pour les relations associatives
     */
    public function scopeAssociative($query)
    {
        return $query->where('relation_type', 'related');
    }

    /**
     * Scope pour les relations de mapping
     */
    public function scopeMapping($query)
    {
        return $query->whereIn('relation_type', ['exactMatch', 'closeMatch', 'broadMatch', 'narrowMatch', 'relatedMatch']);
    }

    /**
     * Scope pour les relations broader
     */
    public function scopeBroader($query)
    {
        return $query->where('relation_type', 'broader');
    }

    /**
     * Scope pour les relations narrower
     */
    public function scopeNarrower($query)
    {
        return $query->where('relation_type', 'narrower');
    }

    /**
     * Scope pour les relations related
     */
    public function scopeRelated($query)
    {
        return $query->where('relation_type', 'related');
    }
}
