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
        'name',
        'slug',
        'content',
        'meta_description',
        'meta_keywords',
        'status',
        'featured_image_path',
        'author_id',
        'order',
        'parent_id',
        'is_published',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function children()
    {
        return $this->hasMany(PublicPage::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(PublicPage::class, 'parent_id');
    }
}
