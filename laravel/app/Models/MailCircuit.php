<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailCircuit extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_REVISION = 'revision_requested';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'mail_id', 'template_code', 'template_name', 'status', 'mode', 'version',
        'metadata', 'initiated_by', 'completed_by', 'started_at', 'completed_at',
        'rejected_at', 'cancelled_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'version' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function steps()
    {
        return $this->hasMany(MailCircuitStep::class)->orderBy('stage')->orderBy('sequence');
    }

    public function actions()
    {
        return $this->hasMany(MailCircuitAction::class)->latest('created_at');
    }

    public function currentSteps()
    {
        return $this->steps()->whereIn('status', [MailCircuitStep::STATUS_PENDING, MailCircuitStep::STATUS_EXPIRED]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_BLOCKED, self::STATUS_REVISION], true);
    }
}
