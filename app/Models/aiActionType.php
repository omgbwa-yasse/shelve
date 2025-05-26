<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiActionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'required_fields',
        'optional_fields',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'required_fields' => 'json',
        'optional_fields' => 'json',
        'validation_rules' => 'json',
        'is_active' => 'boolean',
    ];

    // Relations
    public function actions()
    {
        return $this->hasMany(AiAction::class, 'action_type', 'name');
    }

    public function promptTemplates()
    {
        return $this->hasMany(AiPromptTemplate::class);
    }

    public function integrations()
    {
        return $this->hasMany(AiIntegration::class);
    }

    public function trainingData()
    {
        return $this->hasMany(AiTrainingData::class);
    }
}
