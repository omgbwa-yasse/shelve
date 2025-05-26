<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_chat_id',
        'resource_type',
        'resource_id',
        'content_used',
    ];

    protected $casts = [
        'content_used' => 'json',
    ];

    // Relations
    public function chat()
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }

    // Polymorphic relation
    public function resource()
    {
        return $this->morphTo();
    }
}
