<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskContainer extends Model
{
    use HasFactory;

    protected $table = 'task_container';

    protected $fillable = ['task_id', 'container_id'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }
}
