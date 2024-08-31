<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = ['name', 'description', 'duration', 'task_type_id', 'task_status_id'];

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

    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'task_mail', 'task_id', 'mail_id');
    }

    public function containers()
    {
        return $this->belongsToMany(Container::class, 'task_container', 'task_id', 'container_id');
    }

    public function records()
    {
        return $this->belongsToMany(Record::class, 'task_record', 'task_id', 'record_id');
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'task_attachment', 'task_id', 'attachment_id');
    }
}
