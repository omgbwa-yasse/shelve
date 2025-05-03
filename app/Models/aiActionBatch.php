<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiActionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actions()
    {
        return $this->belongsToMany(AiAction::class, 'ai_batch_actions', 'batch_id', 'action_id')
                    ->withPivot('sequence')
                    ->withTimestamps()
                    ->orderBy('ai_batch_actions.sequence');
    }
}
