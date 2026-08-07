<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Commentaire générique sur une notice — seul l'auteur peut modifier/supprimer
 * le sien — étape 8.
 */
class RecordComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'record_id',
        'user_id',
        'content',
        'updated_by',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOwnedBy(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $userId !== null && (int) $this->user_id === (int) $userId;
    }
}
