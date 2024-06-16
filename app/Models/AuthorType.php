<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorType extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function authors()
    {
        return $this->hasMany(Author::class);
    }


}
