<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    use HasFactory;
    use Searchable;

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
        'is_archived'
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

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'mail_author', 'mail_id', 'author_id')
                    ->withTimestamps();
    }

    public function relatedMails()
    {
        return $this->belongsToMany(Mail::class, 'mail_related', 'mail_id', 'mail_related_id')
                    ->withTimestamps();
    }

    public function archives()
    {
        return $this->hasMany(MailArchive::class); // 'mail_id' est implicite
    }

    public function containers()
    {
        return $this->belongsToMany(container::class, 'mail_archives', 'mail_id', 'container_id')
                    ->withPivot('archived_by', 'document_type')
                    ->withTimestamps();
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_mail_transactions', 'mail_transaction_id', 'dolly_id')
            ->withTimestamps();
    }
}
