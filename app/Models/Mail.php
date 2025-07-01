<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MailStatusEnum;
use App\Enums\NotificationTypeEnum;
use Illuminate\Support\Facades\Auth;

class Mail extends Model
{
    use HasFactory;
    use Searchable;


    const TYPE_INTERNAL = 'internal';
    const TYPE_INCOMING = 'incoming';
    const TYPE_OUTGOING = 'outgoing';



    protected $fillable = [
        'code',
        'name',
        'date',
        'description',
        'document_type',
        'status',
        'priority_id',
        'typology_id',
        'action_id',
        'sender_user_id',
        'sender_organisation_id',
        'recipient_user_id',
        'recipient_organisation_id',
        'is_archived',
        'mail_type',
        'deadline',
        'processed_at',
        'assigned_to',
        'assigned_organisation_id',
        'assigned_at',
        'estimated_processing_time'
    ];

    protected $casts = [
        'date' => 'datetime',
        'deadline' => 'datetime',
        'processed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_archived' => 'boolean',
        'estimated_processing_time' => 'integer', // en minutes
        'status' => MailStatusEnum::class,
    ];

    public $timestamps = true;

    public function priority()
    {
        return $this->belongsTo(MailPriority::class, 'priority_id'); // 'priority_id' est implicite
    }


    public function typology()
    {
        return $this->belongsTo(MailTypology::class, 'typology_id'); // 'typology_id' est implicite
    }

    public function action()
    {
        return $this->belongsTo(MailAction::class, 'action_id'); // 'action_id' est implicite
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'sender_organisation_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function recipientOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'recipient_organisation_id');
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'mail_attachment', 'mail_id', 'attachment_id')
                    ->withPivot('added_by')
                    ->withTimestamps();
    }

    public function relatedMails()
    {
        return $this->belongsToMany(Mail::class, 'mail_related', 'mail_id', 'mail_related_id')
                    ->withTimestamps();
    }

    public function archives()
    {
        return $this->hasMany(MailArchive::class, 'mail_id');
    }

    public function containers()
    {
        return $this->belongsToMany(MailContainer::class, 'mail_archives', 'mail_id', 'container_id')
                    ->withPivot('archived_by', 'document_type')
                    ->withTimestamps();
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_mails', 'mail_id', 'dolly_id')
            ->withTimestamps();
    }

    // Relations pour le système de tracking
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'assigned_organisation_id');
    }

    public function workflow()
    {
        return $this->hasOne(MailWorkflow::class);
    }

    public function histories()
    {
        return $this->hasMany(MailHistory::class)->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(MailNotification::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'mail_author', 'mail_id', 'author_id')
                    ->withTimestamps();
    }


    /*

        * Scopes for filtering mails based on their type
        * These scopes can be used in queries to filter mails by their type
        * For example: Mail::internal()->get() will return all internal mails
        *
        * @param \Illuminate\Database\Eloquent\Builder $query
        * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeInternal($query)
    {
        return $query->where('mail_type', self::TYPE_INTERNAL);
    }

    public function scopeIncoming($query)
    {
        return $query->where('mail_type', self::TYPE_INCOMING);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('mail_type', self::TYPE_OUTGOING);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Check if the mail is of a specific type
     *
     * @param string $type
     * @return bool
     *
     */


    public function isInternal()
    {
        return $this->mail_type === self::TYPE_INTERNAL;
    }

    public function isIncoming()
    {
        return $this->mail_type === self::TYPE_INCOMING;
    }

    public function isOutgoing()
    {
        return $this->mail_type === self::TYPE_OUTGOING;
    }

    // === WORKFLOW & STATUS METHODS ===

    public function initializeWorkflow()
    {
        if (!$this->workflow) {
            return $this->workflow()->create([
                'current_status' => $this->status ?? MailStatusEnum::DRAFT,
                'current_assignee_id' => $this->assigned_to,
                'deadline' => $this->deadline,
                'approval_required' => $this->requiresApproval(),
            ]);
        }
        return $this->workflow;
    }

    public function updateStatus(MailStatusEnum $newStatus, $reason = null)
    {
        $workflow = $this->initializeWorkflow();
        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);
        $workflow->updateStatus($newStatus, $reason);

        // Créer une notification si nécessaire
        $this->createStatusChangeNotification($oldStatus, $newStatus);

        return $this;
    }

    public function assignTo($userId, $reason = null)
    {
        $oldAssignee = $this->assigned_to;

        $this->update([
            'assigned_to' => $userId,
            'assigned_at' => now()
        ]);

        $this->initializeWorkflow()->assignTo($userId, $reason);

        // Créer une notification d'assignation
        $this->createAssignmentNotification($userId);

        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    public function isApproachingDeadline($hours = 24): bool
    {
        return $this->deadline && now()->addHours($hours)->isAfter($this->deadline);
    }

    public function requiresApproval(): bool
    {
        // Logique métier pour déterminer si le courrier nécessite une approbation
        return $this->typology && in_array($this->typology->code, ['CONF', 'LEGAL', 'EXEC']);
    }

    public function getProcessingTimeAttribute()
    {
        if (!$this->processed_at || !$this->created_at) {
            return null;
        }
        return $this->created_at->diffInMinutes($this->processed_at);
    }

    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->deadline) {
            return null;
        }
        return now()->diffInDays($this->deadline, false);
    }

    // === NOTIFICATION METHODS ===

    private function createStatusChangeNotification($oldStatus, $newStatus)
    {
        if ($this->assigned_to) {
            $this->notifications()->create([
                'user_id' => $this->assigned_to,
                'type' => NotificationTypeEnum::MAIL_STATUS_CHANGED,
                'title' => 'Statut modifié',
                'message' => "Le statut du courrier {$this->code} est passé de {$oldStatus->label()} à {$newStatus->label()}",
                'priority' => NotificationTypeEnum::MAIL_STATUS_CHANGED->priority(),
                'data' => [
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'mail_id' => $this->id,
                    'mail_code' => $this->code,
                ]
            ]);
        }
    }

    private function createAssignmentNotification($userId)
    {
        $this->notifications()->create([
            'user_id' => $userId,
            'type' => NotificationTypeEnum::MAIL_ASSIGNED,
            'title' => 'Nouveau courrier assigné',
            'message' => "Le courrier {$this->code} vous a été assigné",
            'priority' => NotificationTypeEnum::MAIL_ASSIGNED->priority(),
            'data' => [
                'mail_id' => $this->id,
                'mail_code' => $this->code,
                'deadline' => $this->deadline?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function createDeadlineNotification($type = NotificationTypeEnum::MAIL_DEADLINE_APPROACHING)
    {
        if ($this->assigned_to) {
            $this->notifications()->create([
                'user_id' => $this->assigned_to,
                'type' => $type,
                'title' => $type->title(),
                'message' => $type === NotificationTypeEnum::MAIL_DEADLINE_APPROACHING
                    ? "Le courrier {$this->code} approche de son échéance"
                    : "Le courrier {$this->code} est en retard",
                'priority' => $type->priority(),
                'data' => [
                    'mail_id' => $this->id,
                    'mail_code' => $this->code,
                    'deadline' => $this->deadline?->format('Y-m-d H:i:s'),
                ]
            ]);
        }
    }

    // === AUDIT METHODS ===

    public function logAction($action, $fieldChanged = null, $oldValue = null, $newValue = null, $description = null)
    {
        return MailHistory::logAction($this->id, $action, $fieldChanged, $oldValue, $newValue, $description);
    }

    public function getAuditTrail()
    {
        return $this->histories()->with('user')->get();
    }

    // === SCOPES ===

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('deadline')
                    ->where('deadline', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeApproachingDeadline($query, $hours = 24)
    {
        return $query->whereNotNull('deadline')
                    ->where('deadline', '>', now())
                    ->where('deadline', '<=', now()->addHours($hours))
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // === BOOT METHOD FOR MODEL EVENTS ===

    protected static function boot()
    {
        parent::boot();

        // Événements du modèle pour le tracking automatique
        static::created(function ($mail) {
            $mail->logAction('created', null, null, null, 'Courrier créé');
            $mail->initializeWorkflow();
        });

        static::updated(function ($mail) {
            $changes = $mail->getChanges();
            foreach ($changes as $field => $newValue) {
                if ($field !== 'updated_at') {
                    $oldValue = $mail->getOriginal($field);
                    $mail->logAction('updated', $field, $oldValue, $newValue, "Champ {$field} modifié");
                }
            }
        });

        static::deleted(function ($mail) {
            $mail->logAction('deleted', null, null, null, 'Courrier supprimé');
        });
    }
}
