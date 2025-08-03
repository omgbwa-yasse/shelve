<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communicability extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'name',
        'description',
        'communicability_period_years',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}


