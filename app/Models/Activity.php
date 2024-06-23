<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

     protected $table = 'activities';

    protected $fillable = [
        'code',
        'name',
        'observation',
        'parent_id',
        'communicability_id',
    ];


    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }


    public function communicability()
    {
        return $this->belongsTo(Communicability::class);
    }


    public function children()
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }


}
