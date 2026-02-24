<?php

namespace App\Models;
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
        return $this->status === 'returned';
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
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la communication est approuvée
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Vérifier si la communication est rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Vérifier si la communication est en consultation
     */
    public function isInConsultation(): bool
    {
        return $this->status === 'in_consultation';
    }

    /**
     * Obtenir le statut suivant logique selon l'action
     */
    public function getNextStatus(string $action): string
    {
        return match ($action) {
            'validate' => $this->status === 'pending' ? 'approved' : $this->status,
            'reject' => in_array($this->status, ['pending', 'approved']) ? 'rejected' : $this->status,
            'transmit' => $this->status === 'approved' ? 'in_consultation' : $this->status,
            'return' => $this->status === 'in_consultation' ? 'returned' : $this->status,
            'cancel_return' => $this->status === 'returned' ? 'in_consultation' : $this->status,
            default => $this->status,
        };
    }

    /**
     * Changer le statut avec validation
     */
    public function changeStatus(string $action): bool
    {
        $newStatus = $this->getNextStatus($action);
        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);
            return true;
        }
        return false;
    }
}
