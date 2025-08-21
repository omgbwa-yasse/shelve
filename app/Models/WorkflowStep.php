<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_template_id',
        'name',
        'description',
        'order_index',
        'step_type',
        'estimated_hours',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Le template de workflow auquel appartient cette étape
     */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /**
     * Les assignations possibles pour cette étape
     */
    public function assignments()
    {
        return $this->hasMany(WorkflowStepAssignment::class);
    }

    /**
     * Les instances de cette étape dans les workflows actifs
     */
    public function instances()
    {
        return $this->hasMany(WorkflowStepInstance::class);
    }

    /**
     * Récupérer les étapes par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('step_type', $type);
    }

    /**
     * Récupérer les étapes requises
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
