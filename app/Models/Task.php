<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'start_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'category_id',
        'assigned_to_organisation_id',
        'assigned_to_user_id',
        'assignment_type',
        'created_by',
        'mail_id',
        'workflow_step_instance_id',
        'parent_task_id',
        'tags',
        'custom_fields',
        'completion_notes',
        'assignment_notes',
        'name',
        'duration',
        'task_type_id'
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'assignment_type' => AssignmentType::class,
        'due_date' => 'datetime',
        'start_date' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
        'custom_fields' => 'array',
    ];

    public function taskType()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    /**
     * La catégorie de cette tâche
     */
    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'task_organisations', 'task_id', 'organisation_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_users', 'task_id', 'user_id');
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'task_attachment', 'task_id', 'attachment_id');
    }

    public function taskMails()
    {
        return $this->belongsToMany(Mail::class, 'task_mail', 'task_id', 'mail_id');
    }

    public function taskContainers()
    {
        return $this->belongsToMany(Container::class, 'task_container', 'task_id', 'container_id');
    }

    public function taskRecords()
    {
        return $this->belongsToMany(Record::class, 'task_record', 'task_id', 'record_id');
    }

    public function taskRemembers()
    {
        return $this->hasMany(TaskRemember::class);
    }

    public function taskSupervisions()
    {
        return $this->hasMany(TaskSupervision::class);
    }

    /**
     * L'utilisateur assigné à cette tâche (si assignment_type inclut 'user')
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * L'organisation assignée à cette tâche (si assignment_type inclut 'organisation')
     */
    public function assignedOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'assigned_to_organisation_id');
    }

    /**
     * L'utilisateur qui a créé cette tâche
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * L'étape de workflow associée à cette tâche (si elle existe)
     */
    public function workflowStepInstance()
    {
        return $this->belongsTo(WorkflowStepInstance::class, 'workflow_step_instance_id');
    }

    /**
     * La tâche parente (si c'est une sous-tâche)
     */
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Les sous-tâches
     */
    public function childTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Les assignations multiples pour cette tâche
     */
    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * L'historique d'assignation de cette tâche
     */
    public function assignmentHistory()
    {
        return $this->hasMany(TaskAssignmentHistory::class);
    }

    /**
     * Les commentaires sur cette tâche
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Les tâches dont dépend cette tâche
     */
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('dependency_type', 'lag_days')
            ->withTimestamps();
    }

    /**
     * Les tâches qui dépendent de cette tâche
     */
    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withPivot('dependency_type', 'lag_days')
            ->withTimestamps();
    }
}

