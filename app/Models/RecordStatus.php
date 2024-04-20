<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'observation',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function records()
    {
        return $this->hasMany(Record::class); // Assuming you have a Record model that uses this status
    }
}
