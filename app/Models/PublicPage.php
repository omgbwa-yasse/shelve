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
        'name',
        'slug',
        'content',
        'order',
        'parent_id',
        'is_published',
    ];

    protected $casts = [
        'order' => 'integer',
        'parent_id' => 'integer',
        'is_published' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(PublicPage::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PublicPage::class, 'parent_id');
    }
}
