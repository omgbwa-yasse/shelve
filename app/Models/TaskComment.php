<?php

namespace App\Models;

use App\Enums\TaskCommentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
        'type',
        'metadata',
    ];

    protected $casts = [
        'type' => TaskCommentType::class,
        'metadata' => 'array',
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
     * Scope pour filtrer par type de commentaire
     */
    public function scopeOfType($query, TaskCommentType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les commentaires utilisateurs uniquement
     */
    public function scopeUserComments($query)
    {
        return $query->where('type', TaskCommentType::COMMENT);
    }

    /**
     * Scope pour les notifications système uniquement
     */
    public function scopeSystemNotifications($query)
    {
        return $query->where('type', '!=', TaskCommentType::COMMENT);
    }
}
