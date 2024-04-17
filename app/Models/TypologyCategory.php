<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypologyCategory extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(TypologyCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TypologyCategory::class, 'parent_id');
    }
}
