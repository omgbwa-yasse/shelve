<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'concepts';

    protected $fillable = [
        'concept_scheme_id',
        'uri',
        'uri_hash',
        'notation',
        'preferred_label',
        'language',
        'definition',
        'scope_note',
        'history_note',
        'editorial_note',
        'example',
        'change_note',
        'status',
        'iso_status',
        'is_top_concept',
        'category',
        'date_created',
        'date_modified',
        'additional_properties'
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'additional_properties' => 'array',
        'is_top_concept' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec les non-descripteurs
     */
    public function nonDescriptors()
    {
        return $this->hasMany(NonDescriptor::class);
    }

    /**
     * Relation avec les alignements externes
     */
    public function externalAlignments()
    {
        return $this->hasMany(ExternalAlignment::class);
    }

    /**
     * Relations hiérarchiques - Termes plus génériques (parents)
     */
    public function broaderTerms()
    {
        return $this->belongsToMany(Term::class, 'hierarchical_relations', 'narrower_concept_id', 'broader_concept_id');
    }

    /**
     * Relations hiérarchiques - Termes plus spécifiques (enfants)
     */
    public function narrowerTerms()
    {
        return $this->belongsToMany(Term::class, 'hierarchical_relations', 'broader_concept_id', 'narrower_concept_id');
    }

    /**
     * Relations associatives
     */
    public function associatedTerms()
    {
        return $this->belongsToMany(Term::class, 'associative_relations', 'concept1_id', 'concept2_id');
    }

    /**
     * Relations inverses associatives
     */
    public function inverseAssociatedTerms()
    {
        return $this->belongsToMany(Term::class, 'associative_relations', 'concept2_id', 'concept1_id');
    }

    /**
     * Toutes les relations associatives (bidirectionnelles)
     */
    public function allAssociatedTerms()
    {
        return $this->associatedTerms->merge($this->inverseAssociatedTerms);
    }

    /**
     * Traductions vers d'autres langues
     */
    public function translations()
    {
        return $this->belongsToMany(Term::class, 'translations', 'source_term_id', 'target_term_id');
    }

    /**
     * Traductions inverses (vers cette langue)
     */
    public function inverseTranslations()
    {
        return $this->belongsToMany(Term::class, 'translations', 'target_term_id', 'source_term_id');
    }

    /**
     * Utilisateur qui a créé le terme
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur qui a modifié le terme
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Scope pour les termes approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope pour les termes candidats
     */
    public function scopeCandidate($query)
    {
        return $query->where('status', 'candidate');
    }

    /**
     * Scope pour une langue spécifique
     */
    public function scopeInLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope pour la recherche textuelle
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('preferred_label', 'LIKE', "%{$search}%")
              ->orWhere('scope_note', 'LIKE', "%{$search}%")
              ->orWhereHas('nonDescriptors', function ($q) use ($search) {
                  $q->where('non_descriptor_label', 'LIKE', "%{$search}%");
              });
        });
    }

    /**
     * Obtenir le libellé d'affichage
     */
    public function getDisplayLabelAttribute()
    {
        return $this->preferred_label . ($this->notation ? " ({$this->notation})" : '');
    }

    /**
     * Vérifier si le terme a des enfants
     */
    public function hasChildren()
    {
        return $this->narrowerTerms()->count() > 0;
    }

    /**
     * Vérifier si le terme a des parents
     */
    public function hasParents()
    {
        return $this->broaderTerms()->count() > 0;
    }

    /**
     * Obtenir la hiérarchie complète du terme
     */
    public function getHierarchy()
    {
        $hierarchy = [];
        
        // Remonter la hiérarchie
        $currentTerm = $this;
        $parents = [];
        
        while ($currentTerm->hasParents()) {
            $parent = $currentTerm->broaderTerms()->first();
            if ($parent && !in_array($parent->id, array_column($parents, 'id'))) {
                $parents[] = $parent;
                $currentTerm = $parent;
            } else {
                break; // Éviter les boucles infinies
            }
        }
        
        $hierarchy['parents'] = array_reverse($parents);
        $hierarchy['current'] = $this;
        $hierarchy['children'] = $this->narrowerTerms;
        
        return $hierarchy;
    }

    /**
     * Statuts disponibles
     */
    public static function getStatuses()
    {
        return [
            'candidate' => 'Candidat',
            'approved' => 'Approuvé', 
            'deprecated' => 'Déprécié'
        ];
    }

    /**
     * Langues disponibles
     */
    public static function getLanguages()
    {
        return [
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano'
        ];
    }
}
