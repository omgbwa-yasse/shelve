<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordBookAuthor extends Model
{
    use HasFactory;

    protected $table = 'record_book_authors';

    protected $fillable = [
        'book_id',
        'name',
        'role',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(RecordBook::class, 'book_id');
    }

    /**
     * Scopes
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAuthors($query)
    {
        return $query->where('role', 'author');
    }

    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    public function scopeTranslators($query)
    {
        return $query->where('role', 'translator');
    }

    /**
     * Methods
     */
    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isTranslator(): bool
    {
        return $this->role === 'translator';
    }

    public function getRoleLabel(): string
    {
        $roles = [
            'author' => 'Auteur',
            'editor' => 'Éditeur',
            'translator' => 'Traducteur',
            'illustrator' => 'Illustrateur',
            'contributor' => 'Contributeur',
        ];

        return $roles[$this->role] ?? ucfirst($this->role);
    }
}
