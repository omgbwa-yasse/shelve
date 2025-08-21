<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
    ];

    /**
     * La tâche concernée par ce commentaire
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * L'utilisateur qui a créé ce commentaire
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

        /**
     * Scope pour les commentaires ordonnés par date
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
