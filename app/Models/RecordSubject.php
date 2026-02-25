<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_subjects';

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'dewey_class',
        'lcc_class',
        'rameau',
        'lcsh',
        'parent_id',
        'related_subjects',
        'synonyms',
        'total_books',
        'status',
    ];

    protected $casts = [
        'related_subjects' => 'array',
        'synonyms' => 'array',
        'total_books' => 'integer',
    ];

    /**
     * Relation: Sujet parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(RecordSubject::class, 'parent_id');
    }

    /**
     * Relation: Sujets enfants
     */
    public function children(): HasMany
    {
        return $this->hasMany(RecordSubject::class, 'parent_id');
    }

    /**
     * Scope: Sujets actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Sujets racines (sans parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Par classe Dewey
     */
    public function scopeByDewey($query, $deweyClass)
    {
        return $query->where('dewey_class', 'like', $deweyClass . '%');
    }

    /**
     * Scope: Recherche
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Accesseur: Nom complet avec parent
     */
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return "{$this->parent->name} > {$this->name}";
        }
        return $this->name;
    }

    /**
     * Accesseur: Est un sujet racine
     */
    public function getIsRootAttribute(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Accesseur: A des enfants
     */
    public function getHasChildrenAttribute(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Accesseur: Niveau dans la hiérarchie
     */
    public function getLevelAttribute(): int
    {
        $level = 0;
        $current = $this;

        while ($current->parent) {
            $level++;
            $current = $current->parent;
        }

        return $level;
    }

    /**
     * Obtenir tous les ancêtres
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Obtenir tous les descendants
     */
    public function getDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Vérifier si a un synonyme
     */
    public function hasSynonym(string $synonym): bool
    {
        return in_array($synonym, $this->synonyms ?? []);
    }

    /**
     * Vérifier si est un sujet connexe
     */
    public function isRelatedTo(int $subjectId): bool
    {
        return in_array($subjectId, $this->related_subjects ?? []);
    }

    /**
     * Marquer comme déprécié
     */
    public function markAsDeprecated(): void
    {
        $this->update(['status' => 'deprecated']);
    }

    /**
     * Trouver ou créer un sujet par nom
     */
    public static function findOrCreateByName(string $name, array $attributes = []): self
    {
        $subject = self::where('name', $name)->first();

        if ($subject) {
            return $subject;
        }

        return self::create(array_merge(['name' => $name], $attributes));
    }

    /**
     * Statistiques par classe Dewey
     */
    public static function byDeweyStats()
    {
        return self::selectRaw('SUBSTRING(dewey_class, 1, 1) as dewey_main, COUNT(*) as count')
            ->whereNotNull('dewey_class')
            ->groupBy('dewey_main')
            ->orderBy('dewey_main')
            ->get();
    }

    /**
     * Construire l'arbre hiérarchique
     */
    public static function buildTree($parentId = null)
    {
        return self::where('parent_id', $parentId)
            ->with('children')
            ->orderBy('name')
            ->get();
    }
}
