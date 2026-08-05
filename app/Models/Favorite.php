<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Favori polymorphe sur une notice (document ou dossier), personnel ou partagé —
 * étape 8.
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoriteable_type',
        'favoriteable_id',
        'shared',
        'note',
    ];

    protected $casts = [
        'shared' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeShared($query)
    {
        return $query->where('shared', true);
    }
}
