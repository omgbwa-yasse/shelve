<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypologyCategory extends Model
{
    use HasFactory;
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
