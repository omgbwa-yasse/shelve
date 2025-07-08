<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusConceptNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept_id',
        'note_type',
        'note_text',
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
     * Scope pour filtrer par type de note
     */
    public function scopeByType($query, $type)
    {
        return $query->where('note_type', $type);
    }

    /**
     * Scope pour filtrer par langue
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope pour les notes de définition
     */
    public function scopeDefinition($query)
    {
        return $query->where('note_type', 'definition');
    }

    /**
     * Scope pour les notes d'application
     */
    public function scopeScopeNote($query)
    {
        return $query->where('note_type', 'scopeNote');
    }

    /**
     * Scope pour les exemples
     */
    public function scopeExample($query)
    {
        return $query->where('note_type', 'example');
    }

    /**
     * Scope pour les notes historiques
     */
    public function scopeHistoryNote($query)
    {
        return $query->where('note_type', 'historyNote');
    }

    /**
     * Scope pour les notes éditoriales
     */
    public function scopeEditorialNote($query)
    {
        return $query->where('note_type', 'editorialNote');
    }

    /**
     * Scope pour les notes de changement
     */
    public function scopeChangeNote($query)
    {
        return $query->where('note_type', 'changeNote');
    }
}
