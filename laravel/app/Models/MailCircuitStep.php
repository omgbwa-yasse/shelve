<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailCircuitStep extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION = 'revision_requested';
    public const STATUS_ABSTAINED = 'abstained';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'mail_circuit_id', 'step_key', 'label', 'stage', 'sequence', 'validator_type',
        'validator_value', 'assigned_user_id', 'original_assigned_user_id',
        'delegated_from_user_id', 'status', 'is_required', 'is_parallel',
        'condition_key', 'due_at', 'acted_by', 'acted_at', 'comment',
    ];

    protected $casts = [
        'stage' => 'integer',
        'sequence' => 'integer',
        'is_required' => 'boolean',
        'is_parallel' => 'boolean',
        'due_at' => 'datetime',
        'acted_at' => 'datetime',
    ];

    public function circuit()
    {
        return $this->belongsTo(MailCircuit::class, 'mail_circuit_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function originalAssignedUser()
    {
        return $this->belongsTo(User::class, 'original_assigned_user_id');
    }

    public function delegatedFrom()
    {
        return $this->belongsTo(User::class, 'delegated_from_user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function isActionable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_EXPIRED], true);
    }
}
