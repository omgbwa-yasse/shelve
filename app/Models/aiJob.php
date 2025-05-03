<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_type',
        'ai_model_id',
        'status',
        'parameters',
        'input',
        'result',
        'error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'json',
        'result' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function aiModel()
    {
        return $this->belongsTo(AiModel::class);
    }
}
