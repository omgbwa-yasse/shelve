<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MailStatusEnum;
use App\Traits\HasDualOrganisation;
use Illuminate\Support\Facades\Auth;

class Mail extends Model
{
    use HasFactory, Searchable, HasDualOrganisation;

    protected $emitterOrgField = 'sender_organisation_id';
    protected $beneficiaryOrgField = 'recipient_organisation_id';


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
        'sender_type',
        'external_sender_id',
        'external_sender_organization_id',
        'recipient_user_id',
        'recipient_organisation_id',
        'recipient_type',
        'external_recipient_id',
        'external_recipient_organization_id',
        'is_archived',
        'mail_type',
        'deadline',
        'processed_at',
        'assigned_to',
        'assigned_organisation_id',
        'assigned_at',
        'estimated_processing_time',
        'dg_signature_status',
        'dg_signed_by',
        'dg_signed_at',
        'dg_signature_note',
        'explanatory_note',
    ];

    protected $casts = [
        'date' => 'datetime',
        'deadline' => 'datetime',
        'processed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'dg_signed_at' => 'datetime',
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

    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_mail', 'mail_id', 'batch_id');
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

    // DG ayant signé/validé le courrier sortant
    public function dgSigner()
    {
        return $this->belongsTo(User::class, 'dg_signed_by');
    }

    // Relations pour les entités externes
    public function externalSender()
    {
        return $this->belongsTo(ExternalContact::class, 'external_sender_id');
    }

    public function externalSenderOrganization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_sender_organization_id');
    }

    public function externalRecipient()
    {
        return $this->belongsTo(ExternalContact::class, 'external_recipient_id');
    }

    public function externalRecipientOrganization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_recipient_organization_id');
    }

    public function assignedOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'assigned_organisation_id');
    }

    /**
     * Override trait method to also check assigned_organisation_id.
     *
     * Portée hiérarchique : une organisation voit les courriers de sa propre branche
     * (elle-même et ses descendants), ce qui permet au DG — racine de l'organigramme —
     * de consulter et coter l'ensemble des courriers de la structure.
     */
    public function involvesOrganisation(int $organisationId): bool
    {
        $mailOrgIds = array_filter([
            $this->{$this->emitterOrgField},
            $this->{$this->beneficiaryOrgField},
            $this->assigned_organisation_id,
        ]);

        if (in_array($organisationId, array_map('intval', $mailOrgIds), true)) {
            return true;
        }

        // Sinon, l'organisation courante couvre-t-elle (comme ancêtre) l'une des
        // organisations du courrier ?
        foreach ($mailOrgIds as $mailOrgId) {
            $org = Organisation::find($mailOrgId);
            if ($org && $org->ancestors()->contains('id', $organisationId)) {
                return true;
            }
        }

        return false;
    }

    // La relation workflow a été supprimée

    public function histories()
    {
        return $this->hasMany(MailHistory::class)->orderBy('created_at', 'desc');
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

    // La méthode initializeWorkflow a été supprimée

    public function updateStatus(MailStatusEnum $newStatus, $reason = null)
    {
        $this->update(['status' => $newStatus]);
        // La partie workflow a été supprimée

        return $this;
    }

    public function assignTo($userId, $reason = null)
    {
        $this->update([
            'assigned_to' => $userId,
            'assigned_at' => now()
        ]);

        // La partie workflow a été supprimée

        return $this;
    }

    // === WORKFLOW COURRIER (circuits entrant / sortant) ===

    /**
     * Cotation d'un courrier entrant par le DG : affectation à une direction
     * avec une instruction (mail_action), puis passage en cours de traitement.
     */
    public function cote(int $organisationId, ?int $actionId = null, ?string $instruction = null): self
    {
        $this->update([
            'assigned_organisation_id' => $organisationId,
            'action_id' => $actionId ?? $this->action_id,
            'assigned_at' => now(),
            'status' => MailStatusEnum::IN_PROGRESS,
        ]);

        $this->logAction('coted', 'assigned_organisation_id', null, (string) $organisationId, $instruction ?? 'Courrier coté par le DG');

        return $this;
    }

    /**
     * Validation de la réception par le responsable du service destinataire.
     */
    public function confirmReception(int $userId): self
    {
        $this->update([
            'assigned_to' => $userId,
            'processed_at' => now(),
            'status' => MailStatusEnum::COMPLETED,
        ]);

        $this->logAction('reception_confirmed', 'status', null, MailStatusEnum::COMPLETED->value, 'Réception validée par le responsable');

        return $this;
    }

    /**
     * Soumission d'un courrier sortant pour validation hiérarchique (N+1)
     * ou proposition au DG, avec note explicative facultative.
     */
    public function submitForApproval(?string $explanatoryNote = null): self
    {
        $this->update([
            'status' => MailStatusEnum::PENDING_APPROVAL,
            'explanatory_note' => $explanatoryNote ?? $this->explanatory_note,
            'dg_signature_status' => 'pending',
        ]);

        $this->logAction('submitted_for_approval', 'status', null, MailStatusEnum::PENDING_APPROVAL->value, 'Courrier soumis pour validation');

        return $this;
    }

    /**
     * Signature / validation finale par le DG (courrier sortant).
     */
    public function signByDg(int $dgUserId, ?string $note = null): self
    {
        $this->update([
            'dg_signature_status' => 'signed',
            'dg_signed_by' => $dgUserId,
            'dg_signed_at' => now(),
            'dg_signature_note' => $note,
            'status' => MailStatusEnum::TRANSMITTED,
            'processed_at' => now(),
        ]);

        $this->logAction('dg_signed', 'dg_signature_status', null, 'signed', $note ?? 'Courrier signé par le DG');

        return $this;
    }

    /**
     * Rejet par le DG (ou par le N+1) d'un courrier soumis.
     */
    public function rejectByDg(int $dgUserId, ?string $note = null): self
    {
        $this->update([
            'dg_signature_status' => 'rejected',
            'dg_signed_by' => $dgUserId,
            'dg_signed_at' => now(),
            'dg_signature_note' => $note,
            'status' => MailStatusEnum::REJECTED,
        ]);

        $this->logAction('dg_rejected', 'dg_signature_status', null, 'rejected', $note ?? 'Courrier rejeté par le DG');

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

    /**
     * Exclude records that look like factory/demo data.
     * Current heuristic: codes generated by our MailFactory use the pattern 'ML########'.
     * We exclude those when explicitly requested by controllers/routes (e.g., mails.sort).
     */
    public function scopeExcludeFactoryLike($query)
    {
        // Use SQL LIKE with single-character wildcards to match ML + 8 chars
        return $query->where('code', 'not like', 'ML________');
    }

    // === BOOT METHOD FOR MODEL EVENTS ===

    protected static function boot()
    {
        parent::boot();

        // Événements du modèle temporairement désactivés pour debug
        /*
        static::created(function ($mail) {
            $mail->logAction('created', null, null, null, 'Courrier créé');
            // La référence à workflow a été supprimée
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
        */
    }
}
