<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThesaurusConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_id',
        'uri',
        'notation',
        'status',
    ];

    /**
     * Relation avec le schéma parent
     */
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ThesaurusScheme::class, 'scheme_id');
    }

    /**
     * Relation avec les labels
     */
    public function labels(): HasMany
    {
        return $this->hasMany(ThesaurusLabel::class, 'concept_id');
    }

    /**
     * Relation avec les notes
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ThesaurusConceptNote::class, 'concept_id');
    }

    /**
     * Relation avec les propriétés personnalisées
     */
    public function properties(): HasMany
    {
        return $this->hasMany(ThesaurusConceptProperty::class, 'concept_id');
    }

    /**
     * Relations source (où ce concept est la source)
     */
    public function sourceRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'source_concept_id');
    }

    /**
     * Relations cible (où ce concept est la cible)
     */
    public function targetRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'target_concept_id');
    }

    /**
     * Relation avec les records (pivot table)
     */
    public function records(): BelongsToMany
    {
        return $this->belongsToMany(Record::class, 'record_thesaurus_concept', 'concept_id', 'record_id');
    }

    /**
     * Relations hiérarchiques - concepts plus larges
     */
    public function broaderConcepts(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'thesaurus_concept_relations',
            'narrower_concept_id',
            'broader_concept_id'
        )->wherePivot('relation_type', 'broader');
    }

    /**
     * Relations hiérarchiques - concepts plus spécifiques
     */
    public function narrowerConcepts(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'thesaurus_concept_relations',
            'broader_concept_id',
            'narrower_concept_id'
        )->wherePivot('relation_type', 'narrower');
    }

    /**
     * Toutes les relations de concepts
     */
    public function conceptRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'broader_concept_id');
    }

    /**
     * Relations inverses de concepts
     */
    public function inverseConceptRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'narrower_concept_id');
    }

    /**
     * Obtenir le label préféré dans une langue donnée
     */
    public function getPreferredLabel($language = 'fr-fr')
    {
        return $this->labels()
                    ->where('label_type', 'prefLabel')
                    ->where('language', $language)
                    ->first();
    }

    /**
     * Obtenir tous les labels alternatifs dans une langue donnée
     */
    public function getAlternativeLabels($language = 'fr-fr')
    {
        return $this->labels()
                    ->where('label_type', 'altLabel')
                    ->where('language', $language)
                    ->get();
    }

    /**
     * Obtenir une note d'un type spécifique
     */
    public function getNote($noteType, $language = 'fr-fr')
    {
        return $this->notes()
                    ->where('note_type', $noteType)
                    ->where('language', $language)
                    ->first();
    }

    /**
     * Vérifier si c'est un concept de tête
     */
    public function isTopConcept()
    {
        return $this->belongsToMany(ThesaurusScheme::class, 'thesaurus_top_concepts', 'concept_id', 'scheme_id')->exists();
    }

    /**
     * Scope pour filtrer par schéma
     */
    public function scopeByScheme($query, $schemeId)
    {
        return $query->where('scheme_id', $schemeId);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les concepts actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Relation avec les records (pivot table)
     */
    public function records(): BelongsToMany
    {
        return $this->belongsToMany(Record::class, 'record_thesaurus_concept', 'concept_id', 'record_id');
    }

    /**
     * Relations hiérarchiques - concepts plus larges
     */
    public function broaderConcepts(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'thesaurus_concept_relations',
            'narrower_concept_id',
            'broader_concept_id'
        )->wherePivot('relation_type', 'broader');
    }

    /**
     * Relations hiérarchiques - concepts plus spécifiques
     */
    public function narrowerConcepts(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'thesaurus_concept_relations',
            'broader_concept_id',
            'narrower_concept_id'
        )->wherePivot('relation_type', 'narrower');
    }

    /**
     * Toutes les relations de concepts
     */
    public function conceptRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'broader_concept_id');
    }

    /**
     * Relations inverses de concepts
     */
    public function inverseConceptRelations(): HasMany
    {
        return $this->hasMany(ThesaurusConceptRelation::class, 'narrower_concept_id');
    }
}
