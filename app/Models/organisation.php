<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organisation::class, 'parent_id');
    }

    public function actives()
    {
        return $this->hasMany(OrganisationActive::class, 'organisation_id');
    }
}




