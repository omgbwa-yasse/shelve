<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $table = 'prompts';

    protected $fillable = [
        'title',
        'content',
        'is_system',
        'organisation_id',
        'user_id',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'organisation_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Get the organisation that owns the prompt.
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the user that owns the prompt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the prompt.
     */
    public function transactions()
    {
        return $this->hasMany(PromptTransaction::class);
    }
}
