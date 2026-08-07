<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communicability extends Model
{
    use HasFactory;
    protected $table = 'communicabilities';
    protected $fillable = [
        'code',
        'name',
        'description',
        'duration', // Duration in years
        // 'communicability_period_years', // This field is deprecated, use 'duration'
    ];

    /**
     * Get the activities that belong to this communicability.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'communicability_id');
    }
}


