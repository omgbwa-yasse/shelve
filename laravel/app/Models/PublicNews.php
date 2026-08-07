<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicNews extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_news';

    protected $fillable = [
        'name',
        'title',
        'slug',
        'content',
        'summary',
        'image_path',
        'author_id',
        'published_at',
        'status',
        'featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'featured' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
