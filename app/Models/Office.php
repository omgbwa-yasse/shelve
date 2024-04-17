<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = ['poste', 'description', 'organisation_id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
