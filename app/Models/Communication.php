<?php

namespace App\Models;
use App\Enums\CommunicationStatus;
use App\Traits\HasDualOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RecordPhysical;
use App\Models\User;

use Laravel\Scout\Searchable;

class Communication extends Model
{
    use HasFactory, Searchable, HasDualOrganisation;

    /**
     * Dual organisation configuration:
     * - emitter = operator's organisation (who creates the communication)
     * - beneficiary = user's organisation (who receives the communication)
     */
    protected string $emitterOrgField = 'operator_organisation_id';
    protected string $beneficiaryOrgField = 'user_organisation_id';
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
        'status',
    ];

    protected $casts = [
        'status' => \App\Enums\CommunicationStatus::class,
        'return_date' => 'date',
        'return_effective' => 'date',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }


    public function records()
    {
        return $this->belongsToMany(RecordPhysical::class, 'communication_record', 'communication_id', 'record_id')
                    ->withPivot('content', 'is_original', 'return_date', 'return_effective', 'operator_id')
                    ->withTimestamps();
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

    /**
     * Vérifier si la communication est retournée
     */
    public function isReturned(): bool
    {
        return $this->status === CommunicationStatus::RETURNED;
    }

    /**
     * Vérifier si la communication peut être modifiée/supprimée
     */
    public function canBeEdited(): bool
    {
        return !$this->isReturned();
    }

    /**
     * Vérifier si la communication est en attente
     */
    public function isPending(): bool
    {
        return $this->status === CommunicationStatus::PENDING;
    }

    /**
     * Vérifier si la communication est approuvée
     */
    public function isApproved(): bool
    {
        return $this->status === CommunicationStatus::APPROVED;
    }

    /**
     * Vérifier si la communication est rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === CommunicationStatus::REJECTED;
    }

    /**
     * Vérifier si la communication est en consultation
     */
    public function isInConsultation(): bool
    {
        return $this->status === CommunicationStatus::IN_CONSULTATION;
    }

    /**
     * Obtenir le statut suivant logique selon l'action
     */
    public function getNextStatus(string $action): string
    {
        return match ($action) {
            'validate' => $this->status === CommunicationStatus::PENDING ? 'approved' : $this->status->value,
            'reject' => in_array($this->status, [CommunicationStatus::PENDING, CommunicationStatus::APPROVED]) ? 'rejected' : $this->status->value,
            'transmit' => $this->status === CommunicationStatus::APPROVED ? 'in_consultation' : $this->status->value,
            'return' => $this->status === CommunicationStatus::IN_CONSULTATION ? 'returned' : $this->status->value,
            'cancel_return' => $this->status === CommunicationStatus::RETURNED ? 'in_consultation' : $this->status->value,
            default => $this->status->value,
        };
    }

    /**
     * Changer le statut avec validation
     */
    public function changeStatus(string $action): bool
    {
        $newStatus = $this->getNextStatus($action);
        if ($newStatus !== $this->status->value) {
            $this->update(['status' => $newStatus]);
            return true;
        }
        return false;
    }
}
