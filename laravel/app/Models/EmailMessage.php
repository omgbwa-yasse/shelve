<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Message synchronisé depuis un compte IMAP (ou enregistré après envoi SMTP).
 * Miroir local consultable rapidement — la source de vérité reste le serveur
 * de messagerie distant (voir `EmailSyncService`).
 */
class EmailMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_account_id',
        'uid',
        'folder',
        'message_id',
        'in_reply_to',
        'subject',
        'from_address',
        'from_name',
        'to',
        'cc',
        'bcc',
        'body_html',
        'body_text',
        'is_read',
        'is_flagged',
        'is_draft',
        'is_answered',
        'has_attachments',
        'sent_at',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'is_read' => 'boolean',
        'is_flagged' => 'boolean',
        'is_draft' => 'boolean',
        'is_answered' => 'boolean',
        'has_attachments' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(EmailTag::class, 'email_message_email_tag');
    }

    /**
     * Un message n'a pas de colonne `organisation_id` propre : il hérite de
     * celle de son compte. Cet accesseur permet aux Policies (`BasePolicy` /
     * Gate `access-in-organisation`) de restreindre l'accès sans traitement
     * spécial — elles lisent `organisation_id` sur n'importe quel modèle.
     */
    public function getOrganisationIdAttribute(): ?int
    {
        return $this->account?->organisation_id;
    }

    public function scopeFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
