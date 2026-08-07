<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Raccourci vers une notice existante (un alias d'emplacement), sans dupliquer
 * `parent_id` — une notice peut être atteinte via plusieurs raccourcis — étape 8.
 */
class RecordShortcut extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'user_id',
        'label',
        'created_by',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
