<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_name',
        'event_name',
        'hook_type',
        'action_type_id',
        'ai_prompt_template_id',
        'description',
        'is_active',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'json',
    ];

    // Relations
    public function actionType()
    {
        return $this->belongsTo(AiActionType::class);
    }

    public function promptTemplate()
    {
        return $this->belongsTo(AiPromptTemplate::class, 'ai_prompt_template_id');
    }
}
