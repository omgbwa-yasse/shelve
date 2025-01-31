<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'instruction',
        'is_public',
        'is_draft',
        'is_archived',
        'is_system',
        'user_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_draft' => 'boolean',
        'is_archived' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

}
