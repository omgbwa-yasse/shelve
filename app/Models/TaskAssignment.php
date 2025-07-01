<?php

namespace App\Models;

use App\Enums\TaskAssigneeRole;
use App\Enums\TaskAssigneeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'assignee_user_id',
        'assignee_organisation_id',
        'assignee_type',
        'role',
        'allocation_percentage',
        'assigned_at',
        'assigned_by',
        'assignment_reason',
    ];

    protected $casts = [
        'assignee_type' => TaskAssigneeType::class,
        'role' => TaskAssigneeRole::class,
        'allocation_percentage' => 'integer',
        'assigned_at' => 'datetime',
    ];

    /**
     * La tâche à laquelle est liée cette assignation
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
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
     * L'utilisateur qui a effectué l'assignation
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope pour filtrer par type d'assigné
     */
    public function scopeOfType($query, TaskAssigneeType $type)
    {
        return $query->where('assignee_type', $type);
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeWithRole($query, TaskAssigneeRole $role)
    {
        return $query->where('role', $role);
    }
}
