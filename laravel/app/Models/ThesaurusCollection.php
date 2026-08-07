<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThesaurusCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_id',
        'uri',
        'ordered',
    ];

    protected $casts = [
        'ordered' => 'boolean',
    ];

    /**
     * Relation avec le schéma parent
     */
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ThesaurusScheme::class, 'scheme_id');
    }

    /**
     * Relation avec les labels de la collection
     */
    public function labels(): HasMany
    {
        return $this->hasMany(ThesaurusCollectionLabel::class, 'collection_id');
    }

    /**
     * Relation avec les concepts membres
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'thesaurus_collection_members',
            'collection_id',
            'concept_id'
        )->withPivot('position')->orderBy('position');
    }

    /**
     * Relation avec les collections parentes (collection contenant cette collection)
     */
    public function parentCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusCollection::class,
            'thesaurus_nested_collections',
            'child_collection_id',
            'parent_collection_id'
        )->withPivot('position');
    }

    /**
     * Relation avec les sous-collections
     */
    public function childCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusCollection::class,
            'thesaurus_nested_collections',
            'parent_collection_id',
            'child_collection_id'
        )->withPivot('position')->orderBy('position');
    }

    /**
     * Indique si la collection est une collection ordonnée (skos:OrderedCollection)
     */
    public function isOrdered(): bool
    {
        return $this->ordered === true;
    }

    /**
     * Scope pour les collections ordonnées
     */
    public function scopeOrdered($query)
    {
        return $query->where('ordered', true);
    }

    /**
     * Scope pour les collections non-ordonnées
     */
    public function scopeUnordered($query)
    {
        return $query->where('ordered', false);
    }
}
