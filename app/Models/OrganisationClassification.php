<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationClassification extends Model
{
    protected $fillable = ['organisation_id', 'classification_id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }
}
