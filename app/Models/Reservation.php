<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'content',
        'operator_id',
        'operator_organisation_id',
        'user_id',
        'user_organisation_id',
        'status_id',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function records()
    {
        return $this->belongsToMany(record::class,'reservation_record','reservation_id');
    }

    public function status()
    {
        return $this->belongsTo(ReservationStatus::class, 'status_id');
    }

    public function userOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'user_organisation_id');
    }

    public function operatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'operator_organisation_id');
    }
}
