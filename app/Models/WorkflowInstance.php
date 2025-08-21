<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_template_id',
        'mail_id',
        'status',
        'current_step_id',
        'initiated_by',
        'started_at',
        'completed_at',
        'due_date',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Le template de workflow sur lequel est basée cette instance
     */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /**
     * Le courrier associé à cette instance de workflow
     */
    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }

    /**
     * L'étape actuelle du workflow
     */
    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    /**
     * L'utilisateur qui a initié ce workflow
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Les étapes de ce workflow (historique et actuelle)
     */
    public function stepInstances()
    {
        return $this->hasMany(WorkflowStepInstance::class);
    }

    /**
     * Les tâches associées à ce workflow
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Récupérer les workflows par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Récupérer les workflows en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Récupérer les workflows en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Récupérer les workflows dont l'échéance approche (dans les prochains jours)
     */
    public function scopeApproachingDeadline($query, $days = 3)
    {
        $now = Carbon::now();
        $future = Carbon::now()->addDays($days);

        return $query->whereBetween('due_date', [$now, $future])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Récupérer les workflows assignés à un utilisateur
     */
    public function scopeAssignedToUser($query, $userId)
    {
        return $query->whereHas('stepInstances', function ($q) use ($userId) {
            $q->where('assigned_to_user_id', $userId)
              ->whereNotIn('status', ['completed', 'skipped']);
        });
    }

    /**
     * Récupérer les workflows assignés à une organisation
     */
    public function scopeAssignedToOrganisation($query, $organisationId)
    {
        return $query->whereHas('stepInstances', function ($q) use ($organisationId) {
            $q->where('assigned_to_organisation_id', $organisationId)
              ->whereNotIn('status', ['completed', 'skipped']);
        });
    }
}
