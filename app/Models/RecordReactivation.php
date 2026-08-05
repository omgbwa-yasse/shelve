<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordReactivation extends Model
{
    use HasFactory, BelongsToOrganisation;

    protected $fillable = [
        'record_id',
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
        return $this->belongsTo(Record::class, 'record_id');
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

    /**
     * Portée organisation (R03) : les demandes de réactivation sont org-scopées par
     * `organisation_id` (trait BelongsToOrganisation). Une demande hors de l'organisation
     * courante répond 404, jamais 403 (motif D03).
     */
    public function scopeInOrganisation($query, int $organisationId)
    {
        return $query->where($this->getTable() . '.organisation_id', $organisationId);
    }
}
