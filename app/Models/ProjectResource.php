<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ressource de projet — humaine, financière, matérielle ou informationnelle.
 * Table unique à discriminant `type` plutôt que 4 tables séparées (voir
 * migration `2026_08_05_100700_add_project_management_features.php`).
 */
class ProjectResource extends Model
{
    use HasFactory;

    public const TYPE_HUMAN = 'human';
    public const TYPE_FINANCIAL = 'financial';
    public const TYPE_MATERIAL = 'material';
    public const TYPE_INFORMATIONAL = 'informational';

    protected $fillable = [
        'project_id',
        'type',
        'name',
        'user_id',
        'role',
        'allocation_percent',
        'unit_cost',
        'quantity',
        'planned_amount',
        'actual_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'allocation_percent' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'quantity' => 'decimal:2',
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getIsOverBudgetAttribute(): bool
    {
        if ($this->planned_amount === null || $this->actual_amount === null) {
            return false;
        }

        return (float) $this->actual_amount > (float) $this->planned_amount;
    }
}
