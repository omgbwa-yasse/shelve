<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordAuthor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_authors';

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'pseudonym',
        'birth_year',
        'death_year',
        'birth_place',
        'nationality',
        'biography',
        'specializations',
        'website',
        'photo',
        'orcid',
        'isni',
        'viaf',
        'total_books',
        'total_works',
        'metadata',
        'status',
    ];

    protected $casts = [
        'birth_year' => 'integer',
        'death_year' => 'integer',
        'total_books' => 'integer',
        'total_works' => 'integer',
        'specializations' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relation many-to-many avec les livres
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(RecordBook::class, 'record_author_book', 'author_id', 'book_id')
            ->withPivot('role', 'display_order', 'notes')
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    /**
     * Livres où l'auteur est auteur principal
     */
    public function authoredBooks(): BelongsToMany
    {
        return $this->books()->wherePivot('role', 'author');
    }

    /**
     * Livres édités
     */
    public function editedBooks(): BelongsToMany
    {
        return $this->books()->wherePivot('role', 'editor');
    }

    /**
     * Livres traduits
     */
    public function translatedBooks(): BelongsToMany
    {
        return $this->books()->wherePivot('role', 'translator');
    }

    /**
     * Scope: Auteurs vivants
     */
    public function scopeAlive($query)
    {
        return $query->where('status', 'active')->whereNull('death_year');
    }

    /**
     * Scope: Auteurs décédés
     */
    public function scopeDeceased($query)
    {
        return $query->where('status', 'deceased')->orWhereNotNull('death_year');
    }

    /**
     * Scope: Par nationalité
     */
    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    /**
     * Scope: Recherche
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('pseudonym', 'like', "%{$search}%")
                ->orWhere('biography', 'like', "%{$search}%");
        });
    }

    /**
     * Accesseur: Nom d'affichage
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->pseudonym) {
            return "{$this->full_name} ({$this->pseudonym})";
        }
        return $this->full_name;
    }

    /**
     * Accesseur: Est vivant
     */
    public function getIsAliveAttribute(): bool
    {
        return $this->status === 'active' && empty($this->death_year);
    }

    /**
     * Accesseur: Âge actuel ou au décès
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_year) {
            return null;
        }

        $endYear = $this->death_year ?? date('Y');
        return $endYear - $this->birth_year;
    }

    /**
     * Accesseur: Durée de vie formatée
     */
    public function getLifespanAttribute(): string
    {
        if (!$this->birth_year) {
            return 'Unknown';
        }

        if ($this->death_year) {
            return "{$this->birth_year} - {$this->death_year}";
        }

        return "{$this->birth_year} - Present";
    }

    /**
     * Marquer comme décédé
     */
    public function markAsDeceased(int $year): void
    {
        $this->update([
            'death_year' => $year,
            'status' => 'deceased',
        ]);
    }

    /**
     * Mettre à jour le nombre de livres
     */
    public function updateBookCount(): void
    {
        $this->update([
            'total_books' => $this->authoredBooks()->count(),
            'total_works' => $this->books()->count(),
        ]);
    }

    /**
     * Obtenir les livres par rôle
     */
    public function getBooksByRole(string $role)
    {
        return $this->books()->wherePivot('role', $role)->get();
    }

    /**
     * Vérifier si a une spécialisation
     */
    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->specializations ?? []);
    }

    /**
     * Trouver ou créer un auteur par nom
     */
    public static function findOrCreateByName(string $name, array $attributes = []): self
    {
        // Essayer de trouver par nom complet
        $author = self::where('full_name', $name)->first();

        if ($author) {
            return $author;
        }

        // Créer un nouvel auteur
        $names = self::parseName($name);

        return self::create(array_merge($names, $attributes));
    }

    /**
     * Parser un nom complet en first_name, last_name
     */
    protected static function parseName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));

        if (count($parts) === 1) {
            return [
                'first_name' => null,
                'last_name' => $parts[0],
                'full_name' => $fullName,
            ];
        }

        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
        ];
    }

    /**
     * Auteurs les plus prolifiques
     */
    public static function mostPublished(int $limit = 10)
    {
        return self::withCount('books')
            ->orderBy('books_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Statistiques par nationalité
     */
    public static function byNationalityStats()
    {
        return self::selectRaw('nationality, COUNT(*) as count')
            ->whereNotNull('nationality')
            ->groupBy('nationality')
            ->orderBy('count', 'desc')
            ->get();
    }
}
