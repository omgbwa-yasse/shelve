<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'meta_keywords',
        'status',
        'featured_image_path',
        'author_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
