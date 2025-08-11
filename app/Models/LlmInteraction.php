<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LlmInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid','request_id','user_id','provider','model','source','status','error_code',
        'prompt_tokens','completion_tokens','total_tokens','latency_ms','temperature','top_p',
        'cost_microusd','started_at','completed_at','metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'temperature' => 'float',
        'top_p' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
