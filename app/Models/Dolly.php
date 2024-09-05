<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dolly extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type_id',
    ];

    public function type()
    {
        return $this->belongsTo(DollyType::class,'type_id');
    }

    public function mail()
    {
        return $this->hasMany(DollyMail::class);
    }

    public function record()
    {
        return $this->hasMany(DollyRecord::class);
    }

    public function communication()
    {
        return $this->hasMany(DollyCommunication::class);
    }

    public function slip()
    {
        return $this->hasMany(DollySlip::class);
    }

    public function slipRecord()
    {
        return $this->hasMany(DollySlipRecord::class);
    }


    public function building()
    {
        return $this->hasMany(DollyBuilding::class);
    }

    public function room()
    {
        return $this->hasMany(DollyRoom::class);
    }

    public function shelf()
    {
        return $this->hasMany(DollyShelf::class);
    }



}
