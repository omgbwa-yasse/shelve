<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'phone1',
        'phone2',
        'email',
        'address',
        'website',
        'fax',
        'other',
        'po_box',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
