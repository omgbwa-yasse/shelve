<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMail extends Model
{
    use HasFactory;

    protected $table = 'task_mail';

    protected $fillable = ['task_id', 'mail_id'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }
}
