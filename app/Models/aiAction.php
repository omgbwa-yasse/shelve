<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_interaction_id',
        'action_type',
        'target_type',
        'target_id',
        'field_name',
        'original_data',
        'modified_data',
        'explanation',
        'metadata',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'original_data' => 'json',
        'modified_data' => 'json',
        'metadata' => 'json',
        'reviewed_at' => 'datetime',
    ];

    // Relations
    public function interaction()
    {
        return $this->belongsTo(AiInteraction::class, 'ai_interaction_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Polymorphic relation with target
    public function target()
    {
        return $this->morphTo();
    }

    public function actionType()
    {
        return $this->belongsTo(AiActionType::class, 'action_type', 'name');
    }

    public function batches()
    {
        return $this->belongsToMany(AiActionBatch::class, 'ai_batch_actions', 'action_id', 'batch_id')
                    ->withPivot('sequence')
                    ->withTimestamps();
    }
}
