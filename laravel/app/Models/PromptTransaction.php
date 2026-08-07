<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_id',
        'started_at',
        'finished_at',
        'model',
        'model_provider',
        'organisation_id',
        'user_id',
        'entity',
        'entity_ids',
        'status',
        'tokens_input',
        'tokens_output',
        'error_message',
        'latency_ms',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'entity_ids' => 'json',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'latency_ms' => 'integer',
    ];

    /**
     * Get the prompt that owns the transaction.
     */
    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Get the organisation that owns the transaction.
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include transactions for a given organisation.
     */
    public function scopeForOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    /**
     * Scope a query to only include transactions with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include transactions for a specific entity type.
     */
    public function scopeForEntity($query, $entity)
    {
        return $query->where('entity', $entity);
    }
}
