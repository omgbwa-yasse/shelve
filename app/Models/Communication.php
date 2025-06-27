<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;
use App\Models\User;
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

    /**
     * Vérifier si la communication est retournée
     */
    public function isReturned()
    {
        return $this->status_id == 5; // 5 = Retournée
    }

    /**
     * Vérifier si la communication peut être modifiée/supprimée
     */
    public function canBeEdited()
    {
        return !$this->isReturned();
    }

    /**
     * Obtenir le statut suivant logique selon l'action
     */
    public function getNextStatusId($action)
    {
        switch ($action) {
            case 'validate':
                return $this->status_id == 1 ? 2 : $this->status_id; // Demande en cours → Validée
            case 'reject':
                return in_array($this->status_id, [1, 2]) ? 3 : $this->status_id; // → Rejetée
            case 'transmit':
                return $this->status_id == 2 ? 4 : $this->status_id; // Validée → En consultation
            case 'return':
                return $this->status_id == 4 ? 5 : $this->status_id; // En consultation → Retournée
            case 'cancel_return':
                return $this->status_id == 5 ? 4 : $this->status_id; // Retournée → En consultation
            default:
                return $this->status_id;
        }
    }

    /**
     * Changer le statut avec validation
     */
    public function changeStatus($action)
    {
        $newStatusId = $this->getNextStatusId($action);

        if ($newStatusId != $this->status_id) {
            $this->update(['status_id' => $newStatusId]);
            return true;
        }

        return false;
    }
}
