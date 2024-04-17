<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\classification;

class OrganisationClassification extends Model
{
    use HasFactory;
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
