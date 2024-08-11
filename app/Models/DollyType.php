<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyType extends Model
{
    use HasFactory;

    protected $table = 'dolly_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public function dollies()
    {
        return $this->hasMany(Dolly::class);
    }
}
