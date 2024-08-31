<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSupervision extends Model
{
    use HasFactory;

    protected $table = 'task_supervision';

    protected $fillable = ['user_id', 'task_assignation', 'task_update', 'task_parent_update', 'task_child_update', 'task_close'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
