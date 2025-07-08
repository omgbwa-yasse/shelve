<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusNamespace extends Model
{
    use HasFactory;

    protected $fillable = [
        'prefix',
        'namespace_uri',
        'description',
    ];

    /**
     * Scope pour filtrer par préfixe
     */
    public function scopeByPrefix($query, $prefix)
    {
        return $query->where('prefix', $prefix);
    }

    /**
     * Scope pour les namespaces standards
     */
    public function scopeStandard($query)
    {
        return $query->whereIn('prefix', ['skos', 'dc', 'dct', 'foaf', 'rdf', 'rdfs', 'owl']);
    }

    /**
     * Scope pour les namespaces personnalisés
     */
    public function scopeCustom($query)
    {
        return $query->whereNotIn('prefix', ['skos', 'dc', 'dct', 'foaf', 'rdf', 'rdfs', 'owl']);
    }
}
