<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rapport d'étape (jalon de suivi) — snapshot périodique de l'avancement d'un projet. */
class ProjectStatusReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'reported_at',
        'percent_complete',
        'summary',
        'risks',
        'created_by',
    ];

    protected $casts = [
        'reported_at' => 'date',
        'percent_complete' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
