<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStepAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_step_id',
        'assignee_user_id',
        'assignee_organisation_id',
    ];

    /**
     * L'étape du workflow à laquelle est liée cette assignation
     */
    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * L'utilisateur assigné (si le type d'assignation est 'user')
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }

    /**
     * L'organisation assignée (si le type d'assignation est 'organisation')
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'assignee_organisation_id');
    }

    /**
     * Récupérer les assignations par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('assignee_type', $type);
    }

    /**
     * Vérifie si l'utilisateur donné est assigné à cette étape
     */
    public function isAssignedToUser($userId)
    {
        return $this->assignee_type === 'user' && $this->assignee_user_id === $userId;
    }

    /**
     * Vérifie si l'organisation donnée est assignée à cette étape
     */
    public function isAssignedToOrganisation($organisationId)
    {
        return $this->assignee_type === 'organisation' && $this->assignee_organisation_id === $organisationId;
    }
}
