<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_templates';

    protected $fillable = [
        'name',
        'description',
        'parameters',
        'values',
        'is_active',
    ];

    protected $casts = [
        'parameters' => 'json',
        'values' => 'json',
        'is_active' => 'boolean',
    ];
}
