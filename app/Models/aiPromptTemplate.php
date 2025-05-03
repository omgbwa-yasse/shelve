<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiPromptTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'template_content',
        'action_type_id',
        'variables',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'json',
        'is_active' => 'boolean',
    ];

    // Relations
    public function actionType()
    {
        return $this->belongsTo(AiActionType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function integrations()
    {
        return $this->hasMany(AiIntegration::class);
    }
}
