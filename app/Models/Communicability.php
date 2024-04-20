<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communicability extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'time',
        'reference',
    ];

    protected $casts = [
        'id' => 'integer',
        'time' => 'integer',
    ];

    public function classifications()
    {
        return $this->hasMany(Classification::class, 'communicability_id');
    }
}
