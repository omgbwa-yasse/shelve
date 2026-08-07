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
        'type',
        'content',
        'variables',
        'parameters',
        'values',
        'status',
        'is_active',
        'author_id',
    ];

    protected $casts = [
        'variables' => 'array',
        'parameters' => 'array',
        'values' => 'array',
        'is_active' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
