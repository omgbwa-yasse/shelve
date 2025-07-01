<?php

namespace App\Models;

use App\Enums\TaskAssignmentActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'previous_user_id',
        'previous_organisation_id',
        'new_user_id',
        'new_organisation_id',
        'action_type',
        'performed_by',
        'reason',
        'effective_date',
        'expiry_date',
        'is_temporary',
    ];

    protected $casts = [
        'action_type' => TaskAssignmentActionType::class,
        'effective_date' => 'datetime',
        'expiry_date' => 'datetime',
        'is_temporary' => 'boolean',
    ];

    /**
     * La tâche concernée par ce changement d'assignation
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * L'ancien utilisateur assigné
     */
    public function previousUser()
    {
        return $this->belongsTo(User::class, 'previous_user_id');
    }

    /**
     * L'ancienne organisation assignée
     */
    public function previousOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'previous_organisation_id');
    }

    /**
     * Le nouvel utilisateur assigné
     */
    public function newUser()
    {
        return $this->belongsTo(User::class, 'new_user_id');
    }

    /**
     * La nouvelle organisation assignée
     */
    public function newOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'new_organisation_id');
    }

    /**
     * L'utilisateur qui a effectué l'action
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope pour filtrer par type d'action
     */
    public function scopeWithActionType($query, TaskAssignmentActionType $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Scope pour les délégations actives
     */
    public function scopeActiveDelegations($query)
    {
        return $query
            ->where('action_type', TaskAssignmentActionType::DELEGATE)
            ->where('is_temporary', true)
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
    }
}
