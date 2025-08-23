<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'name',
        'parallel_name',
        'other_name',
        'lifespan',
        'locations',
        'parent_id',
    ];

    public function authorType()
    {
        return $this->belongsTo(AuthorType::class, 'type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Author::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Author::class, 'parent_id');
    }

    public function contacts()
    {
        return $this->hasMany(AuthorContact::class, 'author_id');
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'mail_author', 'author_id', 'mail_id');
    }


    public function records()
    {
        return $this->belongsToMany(Record::class, 'record_author', 'author_id', 'record_id');
    }

}
