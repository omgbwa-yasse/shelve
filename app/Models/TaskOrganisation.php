<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskOrganisation extends Model
{
    use HasFactory;

    protected $table = 'task_organisations';

    protected $fillable = ['task_id', 'organisation_id'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }
}
