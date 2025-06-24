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
        'slug',
        'content',
        'user_id',
        'is_published',
        'published_at',
        'title',
        'summary',
        'image_path',
        'status',
        'featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'featured' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }
}
