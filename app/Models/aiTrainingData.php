<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTrainingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_type_id',
        'input',
        'expected_output',
        'is_validated',
        'created_by',
        'validated_by',
    ];

    protected $casts = [
        'is_validated' => 'boolean',
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

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
