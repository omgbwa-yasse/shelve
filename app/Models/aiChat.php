<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'ai_model_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function messages()
    {
        return $this->hasMany(AiChatMessage::class);
    }

    public function resources()
    {
        return $this->hasMany(AiResource::class);
    }
}
