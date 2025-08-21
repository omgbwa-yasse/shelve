<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkflowStepInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_instance_id',
        'workflow_step_id',
        'status',
        'assigned_to_user_id',
        'assigned_to_organisation_id',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * L'instance de workflow à laquelle appartient cette étape
     */
    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * L'étape de workflow sur laquelle est basée cette instance
     */
    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * L'utilisateur assigné à cette étape (si assignment_type inclut 'user')
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * L'organisation assignée à cette étape (si assignment_type inclut 'organisation')
     */
    public function assignedOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'assigned_to_organisation_id');
    }

    /**
     * Les tâches associées à cette instance d'étape
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'workflow_step_instance_id');
    }

    /**
     * Récupérer les étapes par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Récupérer les étapes en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Récupérer les étapes en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Récupérer les étapes terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Récupérer les étapes en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
            ->whereNotIn('status', ['completed', 'skipped']);
    }

    /**
     * Récupérer les étapes assignées à un utilisateur
     */
    public function scopeAssignedToUser($query, $userId)
    {
        return $query->where('assigned_to_user_id', $userId)
            ->whereIn('assignment_type', ['user', 'both']);
    }

    /**
     * Récupérer les étapes assignées à une organisation
     */
    public function scopeAssignedToOrganisation($query, $organisationId)
    {
        return $query->where('assigned_to_organisation_id', $organisationId)
            ->whereIn('assignment_type', ['organisation', 'both']);
    }

    /**
     * Récupérer les étapes dont l'échéance approche
     */
    public function scopeApproachingDeadline($query, $days = 3)
    {
        $now = Carbon::now();
        $future = Carbon::now()->addDays($days);

        return $query->whereBetween('due_date', [$now, $future])
            ->whereNotIn('status', ['completed', 'skipped']);
    }
}
