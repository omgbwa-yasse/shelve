<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;


    protected $table = 'attachments';


    protected $fillable = [
        'path',
        'name',
        'crypt',
        'size',
        'creator_id',
    ];




    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_attachment', 'attachment_id', 'task_id');
    }

}
