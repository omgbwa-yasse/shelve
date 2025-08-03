<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communicability extends Model
{
    use HasFactory;
    protected $table = 'communicabilities';
    protected $fillable = [
        'activity_id',
        'name',
        'description',
        'duration', // Duration in years
        // 'communicability_period_years', // This field is deprecated, use 'duration'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}


