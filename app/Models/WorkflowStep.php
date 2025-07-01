<?php

namespace App\Models;

use App\Enums\WorkflowStepType;
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
        'configuration',
        'estimated_duration',
        'is_required',
        'can_be_skipped',
        'conditions',
    ];

    protected $casts = [
        'step_type' => WorkflowStepType::class,
        'configuration' => 'array',
        'is_required' => 'boolean',
        'can_be_skipped' => 'boolean',
        'conditions' => 'array',
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
