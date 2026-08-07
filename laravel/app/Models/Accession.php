<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accession extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'date_creation',
        'date_authorize',
        'observation',
        'organisation_id',
        'accession_status_id',
    ];


    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }


    public function accessionStatus()
    {
        return $this->belongsTo(AccessionStatus::class);
    }


}
