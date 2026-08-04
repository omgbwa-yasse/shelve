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

    /**
     * Champs indexés pour la recherche plein texte.
     * On restreint explicitement à des chaînes : par défaut Scout indexe
     * toArray(), donc toute relation chargée (cotations, pièces jointes...)
     * partait dans l'index sous forme de tableau et faisait échouer le
     * tokenizer lors d'un simple enregistrement du courrier.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'code' => (string) $this->code,
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'document_type' => (string) $this->document_type,
        ];
    }


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
        'activity_id',
        'parent_mail_id',
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

        // Directions cotées (cotation multi-directions) : elles voient le courrier.
        if ($this->exists) {
            foreach ($this->cotations()->pluck('organisation_id') as $cotedOrgId) {
                $mailOrgIds[] = $cotedOrgId;
            }
        }

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
     * Lignes de cotation (une par direction cotée).
     */
    public function cotations()
    {
        return $this->hasMany(MailCotation::class);
    }

    /**
     * Activité du plan de classement dont relève ce courrier.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Activité au titre de laquelle une direction donnée traite ce courrier :
     * celle de sa cotation, à défaut celle du courrier.
     */
    public function activityForOrganisation(?int $organisationId): ?int
    {
        if ($organisationId) {
            $cotation = $this->cotations->firstWhere('organisation_id', $organisationId)
                ?? $this->cotations()->where('organisation_id', $organisationId)->first();

            if ($cotation && $cotation->activity_id) {
                return (int) $cotation->activity_id;
            }
        }

        return $this->activity_id ? (int) $this->activity_id : null;
    }

    /**
     * Directions au titre desquelles cet utilisateur peut traiter ce courrier.
     *
     * Deux sources d'accès :
     *  - son organisation d'appartenance (fonctionnement normal) ;
     *  - un intérim actif — mais alors uniquement si le volet délégué couvre
     *    l'activité au titre de laquelle la direction traite ce courrier.
     *    Un intérimaire ne répond donc qu'aux courriers de son volet.
     *
     * @return array<int, int>
     */
    public function organisationsHandledBy(User $user): array
    {
        $concerned = $this->cotations->pluck('organisation_id')->map(fn ($id) => (int) $id);

        if ($concerned->isEmpty()) {
            $fallback = (int) ($this->assigned_organisation_id ?? $this->recipient_organisation_id);
            $concerned = collect($fallback ? [$fallback] : []);
        }

        $own = (int) $user->current_organisation_id;
        $accessible = $concerned->filter(fn ($orgId) => $orgId === $own);

        foreach (OrganisationInterim::activeVoletsOf($user->id) as $volet) {
            $orgId = (int) $volet->organisation_id;

            if (!$concerned->contains($orgId) || $accessible->contains($orgId)) {
                continue;
            }

            if ($volet->coversActivity($this->activityForOrganisation($orgId))) {
                $accessible->push($orgId);
            }
        }

        return $accessible->unique()->values()->all();
    }

    /**
     * L'utilisateur peut-il agir sur ce courrier (réception, réponse) au titre
     * d'au moins une direction — la sienne ou un volet d'intérim ?
     */
    public function isHandledBy(User $user): bool
    {
        return count($this->organisationsHandledBy($user)) > 0;
    }

    /**
     * Courrier auquel celui-ci répond (ou qu'il prolonge).
     */
    public function parentMail()
    {
        return $this->belongsTo(Mail::class, 'parent_mail_id');
    }

    /**
     * Réponses et suites données à ce courrier : un courrier entrant peut
     * déboucher sur plusieurs courriers (réponse au tiers, note interne...).
     */
    public function replies()
    {
        return $this->hasMany(Mail::class, 'parent_mail_id');
    }

    /**
     * URL de la fiche de ce courrier, selon son type.
     *
     * Plusieurs vues construisaient ce lien à la main en supposant « incoming »,
     * ce qui envoyait les notes internes et les sortants sur la mauvaise route.
     */
    public function showUrl(): string
    {
        $route = match ($this->mail_type) {
            self::TYPE_INCOMING => 'mails.incoming.show',
            self::TYPE_OUTGOING => 'mails.outgoing.show',
            default => 'mail-send.show',
        };

        return route($route, $this->id);
    }

    /**
     * Remonte jusqu'au courrier d'origine de la chaîne.
     */
    public function rootMail(): self
    {
        $current = $this;
        $guard = 0;

        while ($current->parent_mail_id && $guard < 20) {
            $parent = $current->parentMail;
            if (!$parent) {
                break;
            }
            $current = $parent;
            $guard++;
        }

        return $current;
    }

    /**
     * Crée une réponse (ou une suite) rattachée à ce courrier.
     * Le nouveau courrier part en brouillon et suit ensuite le circuit normal
     * (soumission, visas N+1, signature du DG).
     */
    public function createReply(User $author, string $subject, ?string $body = null, ?string $mailType = null): self
    {
        // Répondre à un courrier reçu de l'extérieur produit un courrier sortant ;
        // répondre à un courrier interne produit un courrier interne.
        $type = $mailType ?: ($this->mail_type === self::TYPE_INCOMING ? self::TYPE_OUTGOING : self::TYPE_INTERNAL);

        $reply = static::create([
            'code' => static::generateReplyCode($type),
            'name' => $subject,
            'date' => now(),
            'description' => $body,
            'document_type' => 'original',
            'status' => MailStatusEnum::DRAFT,
            'mail_type' => $type,
            'parent_mail_id' => $this->id,
            'activity_id' => $this->activity_id,
            'typology_id' => $this->typology_id,
            'priority_id' => $this->priority_id,
            'sender_user_id' => $author->id,
            'sender_organisation_id' => $author->current_organisation_id,
            'sender_type' => 'internal',
            // Le destinataire de la réponse est l'expéditeur du courrier d'origine.
            'recipient_type' => $this->sender_type,
            'recipient_user_id' => $this->sender_user_id,
            'recipient_organisation_id' => $this->sender_organisation_id,
            'external_recipient_id' => $this->external_sender_id,
            'external_recipient_organization_id' => $this->external_sender_organization_id,
        ]);

        $this->logAction('replied', 'reply', null, (string) $reply->id, 'Réponse créée : ' . $reply->code . ' — ' . $subject);
        $reply->logAction('created_from_reply', 'reply', null, (string) $this->id, 'Créé en réponse au courrier ' . $this->code);

        return $reply;
    }

    /**
     * Code séquentiel pour un courrier créé en réponse.
     */
    protected static function generateReplyCode(string $type): string
    {
        // Attribution transactionnelle : le format IN-AAAA-001 est inchangé, mais
        // deux réponses créées simultanément ne peuvent plus obtenir le même numéro.
        return app(\App\Services\Mail\MailCodeService::class)->nextForReply($type);
    }

    /**
     * Cotation d'un courrier entrant par le DG à UNE OU PLUSIEURS directions.
     * Crée une ligne de suivi par direction (instruction + réception propres),
     * ce qui permet au DG de suivre la réponse de chaque direction individuellement.
     *
     * @param  int|array<int, int>  $organisationIds
     * @param  array<int, int|null>  $activityIds  activité du plan de classement par direction
     */
    public function cote($organisationIds, ?int $actionId = null, ?string $instruction = null, ?int $cotedBy = null, array $activityIds = []): self
    {
        $orgIds = collect(is_array($organisationIds) ? $organisationIds : [$organisationIds])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($orgIds->isEmpty()) {
            return $this;
        }

        foreach ($orgIds as $orgId) {
            $activityId = isset($activityIds[$orgId]) && $activityIds[$orgId] ? (int) $activityIds[$orgId] : null;

            $existing = MailCotation::where('mail_id', $this->id)
                ->where('organisation_id', $orgId)
                ->first();

            // Une direction qui a déjà validé sa réception n'est pas réinitialisée :
            // le DG peut ajouter une direction sans effacer les réponses obtenues.
            if ($existing && $existing->isCompleted()) {
                continue;
            }

            if ($existing) {
                // Re-coter pour AJOUTER une direction ne doit rien effacer chez les
                // autres : on n'écrase une consigne que si une nouvelle est fournie.
                $existing->update(array_filter([
                    'action_id' => $actionId,
                    'activity_id' => $activityId,
                    'instruction' => $instruction,
                ], fn ($value) => $value !== null && $value !== '') + ['coted_by' => $cotedBy]);

                continue;
            }

            MailCotation::create([
                'mail_id' => $this->id,
                'organisation_id' => $orgId,
                'action_id' => $actionId,
                'activity_id' => $activityId,
                'instruction' => $instruction,
                'status' => MailCotation::STATUS_PENDING,
                'coted_by' => $cotedBy,
            ]);
        }

        // La première direction reste la direction "principale" (compat. requêtes /
        // tableau de bord existants qui lisent assigned_organisation_id).
        // Le courrier ne redevient « en cours » que s'il reste une direction à répondre.
        $stillPending = $this->cotations()->where('status', MailCotation::STATUS_PENDING)->exists();

        $this->update([
            'assigned_organisation_id' => $orgIds->first(),
            'action_id' => $actionId ?? $this->action_id,
            // Le courrier prend l'activité de la première direction cotée
            // (rattachement au plan de classement).
            'activity_id' => ($activityIds[$orgIds->first()] ?? null) ?: $this->activity_id,
            'assigned_at' => now(),
            'status' => $stillPending ? MailStatusEnum::IN_PROGRESS : $this->status,
        ]);

        $names = Organisation::whereIn('id', $orgIds)->pluck('name')->implode(', ');
        $this->logAction('coted', 'cotation', null, $orgIds->implode(','), ($instruction ? $instruction . ' — ' : 'Coté par le DG à : ') . $names);

        return $this;
    }

    /**
     * Validation de la réception pour UNE direction donnée (par son responsable).
     * Le courrier n'est globalement « Terminé » que lorsque toutes les directions
     * cotées ont validé leur réception.
     */
    public function confirmReceptionForOrg(int $organisationId, int $userId): self
    {
        $cotation = $this->cotations()->where('organisation_id', $organisationId)->first();

        if ($cotation) {
            $cotation->update([
                'status' => MailCotation::STATUS_COMPLETED,
                'assigned_to' => $userId,
                'confirmed_at' => now(),
            ]);
        }

        $orgName = Organisation::whereKey($organisationId)->value('name');
        $this->logAction('reception_confirmed', 'cotation', null, (string) $organisationId, 'Réception validée par ' . ($orgName ?? 'la direction'));

        // Statut global : terminé seulement si toutes les cotations sont validées.
        $pending = $this->cotations()->where('status', MailCotation::STATUS_PENDING)->count();
        if ($pending === 0) {
            $this->update(['status' => MailStatusEnum::COMPLETED, 'processed_at' => now()]);
        }

        return $this;
    }

    /**
     * Validation de la réception (rétro-compatible). Si le courrier a des cotations
     * multiples, on valide celle de l'organisation de l'utilisateur.
     */
    public function confirmReception(int $userId, ?int $organisationId = null): self
    {
        if ($this->cotations()->exists()) {
            $orgId = $organisationId ?? optional(User::find($userId))->current_organisation_id;
            return $this->confirmReceptionForOrg((int) $orgId, $userId);
        }

        // Ancien comportement (courrier sans cotation multiple).
        $this->update([
            'assigned_to' => $userId,
            'processed_at' => now(),
            'status' => MailStatusEnum::COMPLETED,
        ]);

        $this->logAction('reception_confirmed', 'status', null, MailStatusEnum::COMPLETED->value, 'Réception validée par le responsable');

        return $this;
    }

    /**
     * L'initiateur du courrier a-t-il besoin d'une validation N+1 avant le DG ?
     * Un agent oui ; un responsable, un directeur ou le DG peuvent soumettre
     * directement au DG (exception : un directeur initie sans visa intermédiaire).
     */
    public function senderNeedsSuperiorValidation(): bool
    {
        $sender = $this->sender_user_id ? User::find($this->sender_user_id) : null;
        if (!$sender) {
            return false;
        }

        $role = $sender->roleInOrganisation($this->sender_organisation_id);

        // Sans rôle identifié, on demande une validation par prudence.
        return !$role || $role->name === 'agent';
    }

    /**
     * Soumission d'un courrier pour validation.
     * - Agent : passe d'abord par la validation N+1 (PENDING_REVIEW).
     * - Responsable / directeur / DG : pas de visa intermédiaire.
     *   → courrier sortant : va directement au DG (PENDING_APPROVAL) ;
     *   → courrier interne : livré directement au service destinataire.
     */
    public function submitForApproval(?string $explanatoryNote = null): self
    {
        $needsN1 = $this->senderNeedsSuperiorValidation();

        if (!$needsN1 && $this->mail_type === self::TYPE_INTERNAL) {
            // Communication inter-services par un responsable/directeur : livraison directe.
            $this->update([
                'status' => MailStatusEnum::IN_PROGRESS,
                'assigned_organisation_id' => $this->recipient_organisation_id,
                'assigned_at' => now(),
                'explanatory_note' => $explanatoryNote ?? $this->explanatory_note,
            ]);

            $this->logAction('submitted_for_approval', 'status', null, MailStatusEnum::IN_PROGRESS->value, 'Communication transmise au service destinataire');

            return $this;
        }

        // Courrier sortant : on démarre la remontée hiérarchique (visa niveau par
        // niveau) à partir du supérieur direct de l'initiateur, jusqu'au DG.
        $this->explanatory_note = $explanatoryNote ?? $this->explanatory_note;
        $this->dg_signature_status = 'pending';

        $sender = $this->sender_user_id ? User::find($this->sender_user_id) : null;
        $firstValidator = ($sender && $needsN1)
            ? $sender->hierarchicalSuperior($this->sender_organisation_id)
            : null; // pas de visa intermédiaire (responsable/directeur) → directement au DG

        return $this->routeToNextValidatorOrDg($firstValidator, 'submitted_for_approval', 'Courrier soumis pour validation');
    }

    /**
     * Oriente le courrier sortant vers le prochain validateur hiérarchique, ou
     * vers la signature du DG si le sommet de la chaîne est atteint.
     * Le validateur courant est mémorisé dans `assigned_to`.
     */
    private function routeToNextValidatorOrDg(?User $validator, string $action, string $baseDesc): self
    {
        $reachedDg = !$validator
            || $validator->isSuperAdmin()
            || $validator->hasRoleInOrganisation('DG', $validator->current_organisation_id);

        if ($reachedDg) {
            $this->status = MailStatusEnum::PENDING_APPROVAL;
            $this->assigned_to = null;
            $this->save();
            $this->logAction($action, 'status', null, MailStatusEnum::PENDING_APPROVAL->value, $baseDesc . ' — en attente de signature du DG');
        } else {
            $this->status = MailStatusEnum::PENDING_REVIEW;
            $this->assigned_to = $validator->id; // validateur courant de la remontée
            $this->save();
            $this->logAction($action, 'status', null, MailStatusEnum::PENDING_REVIEW->value, $baseDesc . ' — visa attendu de ' . trim($validator->name . ' ' . ($validator->surname ?? '')));
        }

        return $this;
    }

    /**
     * Validation intermédiaire par le supérieur hiérarchique (N+1).
     * - Courrier sortant : passe à la signature du DG (PENDING_APPROVAL).
     * - Courrier interne (communication inter-services) : est livré au service
     *   destinataire, dont le responsable pourra le coter en interne.
     */
    public function validateBySuperior(int $userId, ?string $note = null): self
    {
        if ($this->mail_type === self::TYPE_INTERNAL) {
            // Livraison au service destinataire : il devient l'organisation assignée.
            $this->update([
                'status' => MailStatusEnum::IN_PROGRESS,
                'assigned_organisation_id' => $this->recipient_organisation_id,
                'assigned_at' => now(),
            ]);

            $this->logAction('n1_validated', 'status', null, MailStatusEnum::IN_PROGRESS->value, $note ?? 'Validé par le supérieur (N+1) et transmis au service destinataire');

            return $this;
        }

        // Courrier sortant : le validateur courant vise, puis on remonte au niveau
        // supérieur (autre visa) ou, si le sommet est atteint, à la signature du DG.
        $validator = User::find($userId);
        $nextValidator = $validator
            ? $validator->hierarchicalSuperior($validator->current_organisation_id)
            : null;

        $validatorLabel = $validator ? trim($validator->name . ' ' . ($validator->surname ?? '')) : 'le supérieur';
        $this->logAction('n1_validated', 'status', null, null, $note ?? ('Visa de ' . $validatorLabel));

        return $this->routeToNextValidatorOrDg($nextValidator, 'n1_escalated', 'Visa accordé');
    }

    /**
     * Affectation (cotation interne) d'un courrier à un agent d'un service,
     * par le responsable de ce service, avec une instruction facultative.
     */
    public function assignToUser(int $userId, ?int $actionId = null, ?string $instruction = null): self
    {
        $this->update([
            'assigned_to' => $userId,
            'action_id' => $actionId ?? $this->action_id,
            'assigned_at' => now(),
            'status' => MailStatusEnum::IN_PROGRESS,
        ]);

        $this->logAction('assigned_to_user', 'assigned_to', null, (string) $userId, $instruction ?? 'Affecté à un agent du service');

        return $this;
    }

    /**
     * Renvoi du courrier à son initiateur pour révision (distinct d'un rejet
     * définitif) : l'initiateur pourra corriger, ajouter des pièces jointes,
     * puis resoumettre. Utilisable par le N+1 comme par le DG.
     */
    public function returnForRevision(int $userId, ?string $note = null): self
    {
        $this->update([
            'status' => MailStatusEnum::REJECTED,
            'dg_signature_status' => 'rejected',
            'dg_signature_note' => $note,
        ]);

        $this->logAction('returned_for_revision', 'status', null, MailStatusEnum::REJECTED->value, $note ?? 'Renvoyé pour révision');

        return $this;
    }

    /**
     * Resoumission par l'initiateur après révision : le courrier reprend la
     * remontée hiérarchique depuis le supérieur direct (ou directement au DG).
     */
    public function resubmit(?string $note = null): self
    {
        $this->dg_signature_status = 'pending';

        $sender = $this->sender_user_id ? User::find($this->sender_user_id) : null;
        $firstValidator = ($sender && $this->senderNeedsSuperiorValidation())
            ? $sender->hierarchicalSuperior($this->sender_organisation_id)
            : null;

        return $this->routeToNextValidatorOrDg($firstValidator, 'resubmitted', $note ?? 'Courrier corrigé et resoumis');
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

    /**
     * Champs dont la modification est journalisée automatiquement.
     *
     * Le statut en est volontairement absent : les méthodes de workflow le tracent
     * déjà avec un libellé métier (« Signé par le DG », « Visa hiérarchique »),
     * bien plus lisible qu'un « Champ status modifié ».
     */
    protected const AUDITED_FIELDS = [
        'name', 'date', 'description', 'document_type', 'deadline',
        'priority_id', 'typology_id', 'activity_id', 'action_id',
        'sender_user_id', 'sender_organisation_id',
        'external_sender_id', 'external_sender_organization_id',
        'recipient_user_id', 'recipient_organisation_id',
        'external_recipient_id', 'external_recipient_organization_id',
    ];

    /**
     * Réduit une valeur à un scalaire sérialisable en JSON.
     *
     * C'est ce qui manquait à l'ancienne implémentation : `old_value` / `new_value`
     * sont castés en json dans MailHistory, or `status` est casté en enum et
     * `date` / `deadline` en Carbon — d'où le plantage qui avait fait désactiver
     * ces événements.
     */
    protected static function scalarizeForAudit($value)
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($mail) {
            $mail->logAction('created', null, null, null, 'Courrier créé');
        });

        static::updated(function ($mail) {
            foreach ($mail->getChanges() as $field => $newValue) {
                if (!in_array($field, self::AUDITED_FIELDS, true)) {
                    continue;
                }

                $mail->logAction(
                    'updated',
                    $field,
                    static::scalarizeForAudit($mail->getOriginal($field)),
                    static::scalarizeForAudit($newValue),
                    "Champ {$field} modifié"
                );
            }
        });

        static::deleted(function ($mail) {
            $mail->logAction('deleted', null, null, null, 'Courrier supprimé');
        });
    }
}
