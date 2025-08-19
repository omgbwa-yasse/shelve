<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'native_name',
    ];

    public function terms()
    {
    // The Term model stores language as an ISO code string in the 'language' column
    // while Language uses 'code' as its PK-like identifier
    return $this->hasMany(Term::class, 'language', 'code');
    }

}
