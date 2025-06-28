<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;
use App\Models\User;
use App\Enums\CommunicationStatus;
use Laravel\Scout\Searchable;


class Communication extends Model
{
    use HasFactory;
    use searchable;
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
        'status' => CommunicationStatus::class,
        'return_date' => 'date',
        'return_effective' => 'date',
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
    public function getNextStatus($action): CommunicationStatus
    {
        return match ($action) {
            'validate' => $this->status === CommunicationStatus::PENDING ? CommunicationStatus::APPROVED : $this->status,
            'reject' => in_array($this->status, [CommunicationStatus::PENDING, CommunicationStatus::APPROVED]) ? CommunicationStatus::REJECTED : $this->status,
            'transmit' => $this->status === CommunicationStatus::APPROVED ? CommunicationStatus::IN_CONSULTATION : $this->status,
            'return' => $this->status === CommunicationStatus::IN_CONSULTATION ? CommunicationStatus::RETURNED : $this->status,
            'cancel_return' => $this->status === CommunicationStatus::RETURNED ? CommunicationStatus::IN_CONSULTATION : $this->status,
            default => $this->status,
        };
    }

    /**
     * Changer le statut avec validation
     */
    public function changeStatus($action): bool
    {
        $newStatus = $this->getNextStatus($action);

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);
            return true;
        }

        return false;
    }
}
