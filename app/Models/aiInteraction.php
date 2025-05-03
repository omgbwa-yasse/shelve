<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_model_id',
        'input',
        'output',
        'parameters',
        'tokens_used',
        'module_type',
        'module_id',
        'status',
        'session_id',
    ];

    protected $casts = [
        'parameters' => 'json',
        'tokens_used' => 'float',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class);
    }

    public function actions()
    {
        return $this->hasMany(AiAction::class);
    }

    public function feedback()
    {
        return $this->hasMany(AiFeedback::class);
    }


}
