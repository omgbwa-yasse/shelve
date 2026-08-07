<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'observation',
    ];


    public $timestamps = false;

    public function accessions()
    {
        return $this->hasMany(Accession::class);
    }



}
