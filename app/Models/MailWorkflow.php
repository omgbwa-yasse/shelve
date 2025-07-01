<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MailStatusEnum;

class MailWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_id',
        'current_status',
        'current_assignee_id',
        'workflow_data',
        'approval_required',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'escalated_at',
        'escalated_to',
        'deadline',
        'auto_escalate_hours',
        'priority_escalation_enabled'
    ];

    protected $casts = [
        'current_status' => MailStatusEnum::class,
        'workflow_data' => 'array',
        'approval_required' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'escalated_at' => 'datetime',
        'deadline' => 'datetime',
        'priority_escalation_enabled' => 'boolean',
        'auto_escalate_hours' => 'integer'
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function currentAssignee()
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function isOverdue(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    public function isApproachingDeadline($hours = 24): bool
    {
        return $this->deadline && now()->addHours($hours)->isAfter($this->deadline);
    }

    public function needsEscalation(): bool
    {
        return $this->auto_escalate_hours &&
               $this->updated_at->addHours($this->auto_escalate_hours)->isPast() &&
               !$this->escalated_at;
    }

    public function canBeApproved(): bool
    {
        return $this->approval_required &&
               $this->current_status === MailStatusEnum::PENDING_APPROVAL &&
               !$this->approved_at &&
               !$this->rejected_at;
    }

    public function approve($userId, $comments = null)
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
            'current_status' => MailStatusEnum::APPROVED
        ]);

        // Log l'approbation
        MailHistory::logAction(
            $this->mail_id,
            'approved',
            'status',
            $this->current_status->value,
            MailStatusEnum::APPROVED->value,
            $comments
        );

        return $this;
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'current_status' => MailStatusEnum::REJECTED
        ]);

        // Log le rejet
        MailHistory::logAction(
            $this->mail_id,
            'rejected',
            'status',
            $this->current_status->value,
            MailStatusEnum::REJECTED->value,
            $reason
        );

        return $this;
    }

    public function escalate($toUserId, $reason = null)
    {
        $this->update([
            'escalated_to' => $toUserId,
            'escalated_at' => now(),
            'current_assignee_id' => $toUserId
        ]);

        // Log l'escalade
        MailHistory::logAction(
            $this->mail_id,
            'escalated',
            'current_assignee_id',
            $this->current_assignee_id,
            $toUserId,
            $reason
        );

        return $this;
    }

    public function updateStatus(MailStatusEnum $newStatus, $reason = null)
    {
        $oldStatus = $this->current_status;

        if (!$oldStatus->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException("Cannot transition from {$oldStatus->value} to {$newStatus->value}");
        }

        $this->update(['current_status' => $newStatus]);

        // Log le changement de statut
        MailHistory::logAction(
            $this->mail_id,
            'status_changed',
            'status',
            $oldStatus->value,
            $newStatus->value,
            $reason
        );

        return $this;
    }

    public function assignTo($userId, $reason = null)
    {
        $oldAssignee = $this->current_assignee_id;

        $this->update(['current_assignee_id' => $userId]);

        // Log l'assignation
        MailHistory::logAction(
            $this->mail_id,
            'assigned',
            'current_assignee_id',
            $oldAssignee,
            $userId,
            $reason
        );

        return $this;
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now());
    }

    public function scopeApproachingDeadline($query, $hours = 24)
    {
        return $query->where('deadline', '>', now())
                    ->where('deadline', '<', now()->addHours($hours));
    }

    public function scopeNeedsEscalation($query)
    {
        return $query->whereNotNull('auto_escalate_hours')
                    ->whereNull('escalated_at')
                    ->whereRaw('TIMESTAMPDIFF(HOUR, updated_at, NOW()) >= auto_escalate_hours');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_required', true)
                    ->where('current_status', MailStatusEnum::PENDING_APPROVAL)
                    ->whereNull('approved_at')
                    ->whereNull('rejected_at');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('current_assignee_id', $userId);
    }
}
