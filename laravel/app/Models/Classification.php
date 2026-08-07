<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'observation',
        'communicability_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'communicability_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Classification::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Classification::class, 'parent_id');
    }

    public function communicability()
    {
        return $this->belongsTo(Communicability::class, 'communicability_id');
    }
}
