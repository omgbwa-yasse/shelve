<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $table = 'prompts';

    protected $fillable = [
        'title',
        'content',
        'is_system',
        'organisation_id',
        'user_id',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'organisation_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Get the organisation that owns the prompt.
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the user that owns the prompt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the prompt.
     */
    public function transactions()
    {
        return $this->hasMany(PromptTransaction::class);
    }

    /**
     * Portée de visibilité (D14) : un prompt est visible si système, s'il appartient
     * à l'organisation courante, ou s'il est personnel (`organisation_id` nul et
     * créé par l'agent). Reprise du `where` de `PromptManagementController::index()`
     * et de `authorizePromptAccess()`.
     */
    public function scopeVisibleTo($query, ?int $organisationId, ?int $userId)
    {
        return $query->where(function ($q) use ($organisationId, $userId) {
            $q->where('is_system', true)
                ->orWhere('organisation_id', $organisationId)
                ->orWhere(fn ($q2) => $q2->whereNull('organisation_id')->where('user_id', $userId));
        });
    }
}
