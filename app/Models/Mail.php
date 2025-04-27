<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public $timestamps = true;

    public function priority()
    {
        return $this->belongsTo(MailPriority::class); // 'priority_id' est implicite
    }


    public function typology()
    {
        return $this->belongsTo(MailTypology::class); // 'typology_id' est implicite
    }

    public function action()
    {
        return $this->belongsTo(MailAction::class); // 'action_id' est implicite
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
}
