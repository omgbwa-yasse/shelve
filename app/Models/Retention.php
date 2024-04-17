<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RetentionSort;

class Retention extends Model
{
    use HasFactory;
    protected $fillable = [
        'duration', 'sort', 'reference', 'retention_sort_id'
    ];

    public function sort()
    {
        return $this->belongsTo(RetentionSort::class);
    }
}
