<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThesaurusScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'uri',
        'identifier',
        'title',
        'description',
        'language',
        'dc_relation',
        'dc_source',
        'issued',
    ];

    protected $casts = [
        'issued' => 'date',
    ];

    /**
     * Relation avec les concepts de ce schéma
     */
    public function concepts(): HasMany
    {
        return $this->hasMany(ThesaurusConcept::class, 'scheme_id');
    }

    /**
     * Relation avec les concepts de tête (top concepts)
     */
    public function topConcepts(): BelongsToMany
    {
        return $this->belongsToMany(ThesaurusConcept::class, 'thesaurus_top_concepts', 'scheme_id', 'concept_id');
    }

    /**
     * Relation avec les organisations
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(ThesaurusOrganization::class, 'thesaurus_scheme_organizations', 'scheme_id', 'organization_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Relation avec les namespaces utilisés
     */
    public function namespaces(): HasMany
    {
        return $this->hasMany(ThesaurusNamespace::class);
    }

    /**
     * Scope pour filtrer par langue
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope pour les schémas actifs
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('title');
    }

    /**
     * Obtenir le nombre total de concepts dans ce schéma
     */
    public function getConceptsCountAttribute()
    {
        return $this->concepts()->count();
    }

    /**
     * Obtenir le titre formaté avec l'identifiant
     */
    public function getFormattedTitleAttribute()
    {
        return $this->identifier ? "[{$this->identifier}] {$this->title}" : $this->title;
    }

    /**
     * Relation avec les collections appartenant à ce schéma
     */
    public function collections(): HasMany
    {
        return $this->hasMany(ThesaurusCollection::class, 'scheme_id');
    }

    /**
     * Relation avec les collections ordonnées
     */
    public function orderedCollections()
    {
        return $this->collections()->where('ordered', true);
    }

    /**
     * Relation avec les collections non-ordonnées
     */
    public function unorderedCollections()
    {
        return $this->collections()->where('ordered', false);
    }
}
