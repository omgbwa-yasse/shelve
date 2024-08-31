<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRemember extends Model
{
    use HasFactory;

    protected $table = 'task_remember';

    protected $fillable = ['date_fix', 'periode', 'date_trigger', 'limit_number', 'limit_date', 'frequence_value', 'frequence_unit', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
