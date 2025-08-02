<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sort;
use App\Models\Activity;

class Retention extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'duration',
        'sort_id',
    ];

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'retention_activity', 'retention_id', 'activity_id');
    }
}
