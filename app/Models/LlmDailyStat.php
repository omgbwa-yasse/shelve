<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LlmDailyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date','provider','model','source','user_id','requests_count','success_count','error_count',
        'total_prompt_tokens','total_completion_tokens','total_tokens','total_cost_microusd','avg_latency_ms','max_latency_ms'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
