<?php

namespace App\Models;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'preferred_label',
        'definition',
        'scope_note',
        'history_note',
        'example',
        'editorial_note',
        'language',
        'category',
        'status',
        'notation',
        'is_top_term',
    ];

    // Les termes génériques (parents) dans les relations hiérarchiques
    public function broaderTerms()
    {
        return $this->belongsToMany(Term::class, 'hierarchical_relations', 'narrower_term_id', 'broader_term_id')
                   ->withPivot('relation_type')
                   ->withTimestamps();
    }

    // Les termes spécifiques (enfants) dans les relations hiérarchiques
    public function narrowerTerms()
    {
        return $this->belongsToMany(Term::class, 'hierarchical_relations', 'broader_term_id', 'narrower_term_id')
                   ->withPivot('relation_type')
                   ->withTimestamps();
    }

    // Les termes associés (TA)
    public function associatedTerms()
    {
        // Les termes où ce terme est term1_id
        $related1 = $this->belongsToMany(Term::class, 'associative_relations', 'term1_id', 'term2_id')
                         ->withPivot('relation_subtype')
                         ->withTimestamps();

        // Les termes où ce terme est term2_id
        $related2 = $this->belongsToMany(Term::class, 'associative_relations', 'term2_id', 'term1_id')
                         ->withPivot('relation_subtype')
                         ->withTimestamps();

        return $related1->union($related2->toBase());
    }

    // Les non-descripteurs (synonymes) associés à ce terme
    public function nonDescriptors()
    {
        return $this->hasMany(NonDescriptor::class, 'descriptor_id');
    }

    // Les traductions de ce terme
    public function translationsSource()
    {
        return $this->belongsToMany(Term::class, 'translations', 'source_term_id', 'target_term_id')
                    ->withTimestamps();
    }

    public function translationsTarget()
    {
        return $this->belongsToMany(Term::class, 'translations', 'target_term_id', 'source_term_id')
                    ->withTimestamps();
    }

    // Les alignements externes
    public function externalAlignments()
    {
        return $this->hasMany(ExternalAlignment::class);
    }

    // Pour compatibilité avec le code existant
    public function records()
    {
        return $this->belongsToMany(Record::class, 'record_term', 'term_id', 'record_id');
    }
}
