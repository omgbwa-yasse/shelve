<?php

namespace App\Models;
use App\Traits\HasDualOrganisation;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organisation;
use App\Models\SlipStatus;

class Slip extends Model
{
    use HasFactory, Searchable, HasDualOrganisation;

    /**
     * Dual organisation configuration:
     * - emitter = officer's organisation (who creates the slip)
     * - beneficiary = user's organisation (who receives the slip)
     */
    protected string $emitterOrgField = 'officer_organisation_id';
    protected string $beneficiaryOrgField = 'user_organisation_id';

    protected $fillable = [
        'code',
        'name',
        'description',
        'transfer_type',
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
        'is_rejected',
        'rejected_date',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'is_received' => 'boolean',
        'is_approved' => 'boolean',
        'is_integrated' => 'boolean',
        'is_rejected' => 'boolean',
        'received_date' => 'datetime',
        'approved_date' => 'datetime',
        'integrated_date' => 'datetime',
        'rejected_date' => 'datetime',
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

    public function rejectedAgent()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function getTransferTypeLabelAttribute(): string
    {
        return $this->transfer_type === 'archival_deposit'
            ? 'Versement au service des archives'
            : 'Transfert entre directions';
    }

    /**
     * Keep the search payload flat. A slip can be saved while its records and
     * source records are eager-loaded during integration; Scout/TNTSearch only
     * accepts scalar values and must never receive these nested relations.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'code' => (string) $this->code,
            'name' => (string) $this->name,
            'description' => (string) ($this->description ?? ''),
            'transfer_type' => (string) ($this->transfer_type ?? ''),
            'officer_organisation_id' => (string) ($this->officer_organisation_id ?? ''),
            'user_organisation_id' => (string) ($this->user_organisation_id ?? ''),
            'slip_status_id' => (string) ($this->slip_status_id ?? ''),
        ];
    }


}
