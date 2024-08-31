<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRecord extends Model
{
    use HasFactory;

    protected $table = 'task_record';

    protected $fillable = ['task_id', 'record_id'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }
}
