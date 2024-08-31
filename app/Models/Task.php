<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = ['name', 'description', 'duration', 'task_status_id', 'task_type_id'];

    public function taskType()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
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

    public function taskRemembers()
    {
        return $this->hasMany(TaskRemember::class);
    }

    public function taskRecords()
    {
        return $this->hasMany(TaskRecord::class);
    }

    public function taskSupervisions()
    {
        return $this->hasMany(TaskSupervision::class);
    }

    public function taskMails()
    {
        return $this->hasMany(TaskMail::class);
    }

    public function taskContainers()
    {
        return $this->hasMany(TaskContainer::class);
    }
}
