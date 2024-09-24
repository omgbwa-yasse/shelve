<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;
use App\Models\User;


class Communication extends Model
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
        'return_date',
        'return_effective',
        'status_id',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }


    public function records()
    {
        return $this->hasMany(communicationRecord::class);
    }

    public function operatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'operator_organisation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'user_organisation_id');
    }

    public function status()
    {
        return $this->belongsTo(CommunicationStatus::class, 'status_id');
    }
}
