<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $table = 'prompts';

    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
        'organisation_id' => 'integer',
        'user_id' => 'integer',
    ];
}
