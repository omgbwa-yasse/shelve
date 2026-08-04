<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordReactivation extends Model
{
    use HasFactory, BelongsToOrganisation;

    protected $fillable = [
        'record_physical_id',
        'organisation_id',
        'previous_status_id',
        'previous_transfer_date',
        'new_transfer_date',
        'reason',
        'rejection_reason',
        'is_approved',
        'requested_by',
        'requested_date',
        'approved_by',
        'approved_date',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'previous_transfer_date' => 'date',
        'new_transfer_date' => 'date',
    ];

    public function record()
    {
        return $this->belongsTo(RecordPhysical::class, 'record_physical_id');
    }

    public function previousStatus()
    {
        return $this->belongsTo(RecordStatus::class, 'previous_status_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
