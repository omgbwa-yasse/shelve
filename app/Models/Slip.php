<?php

namespace App\Models;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organisation;
use App\Models\SlipStatus;

class Slip extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'officer_organisation_id',
        'officer_id',
        'user_organisation_id',
        'user_id',
        'slip_status_id',
        'is_received',
        'received_date',
        'received_by',
        'is_approved',
        'approved_date',
        'approved_by',
        'is_integrated',
        'integrated_date',
        'integrated_by',
    ];


    public function officerOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'officer_organisation_id');
    }


    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }


    public function userOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'user_organisation_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function slipStatus()
    {
        return $this->belongsTo(SlipStatus::class, 'slip_status_id');
    }

    public function records()
    {
        return $this->hasMany(SlipRecord::class, 'slip_id');
    }



    public function receivedAgent()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedAgent()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function integratedAgent()
    {
        return $this->belongsTo(User::class, 'integrated_by');
    }


}
