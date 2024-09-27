<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\MailSubject;
use App\Models\MailBatch;
use App\Models\MailAttachment;
use App\Models\Author;
use App\Models\documentType;
use App\Models\MailContainer;
use App\Models\MailTransaction;

class Mail extends Model
{
    use HasFactory;

    // use Searchable;
    protected $fillable = [
        'code',
        'name',
        'author_id',
        'description',
        'contacts',
        'date',
        'create_by',
        'update_by',
        'mail_priority_id',
        'mail_type_id',
        'mail_typology_id',
        'document_type_id',
    ];
    public $timestamps = true;

    public function priority()
    {
        return $this->belongsTo(MailPriority::class, 'mail_priority_id');
    }


    public function typology()
    {
        return $this->belongsTo(MailTypology::class, 'mail_typology_id');
    }

    public function attachments()
    {
        return $this->belongsToMany(MailAttachment::class, 'mail_attachment', 'mail_id', 'attachment_id');
    }


    public function send()
    {
        return $this->hasMany(MailTransaction::class);
    }

    public function received()
    {
        return $this->hasMany(MailTransaction::class);
    }

    public function container()
    {
        return $this->belongsToMany(MailContainer::class, 'mail_archiving', 'mail_id', 'container_id');
    }

    public function archived()
    {
        return $this->belongsTo(MailArchiving::class, 'mail_id');
    }

    public function type()
    {
        return $this->belongsTo(MailType::class, 'mail_type_id');
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by', 'id');
    }

    public function updator()
    {
        return $this->belongsTo(User::class, 'update_by', 'id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'mail_author', 'mail_id', 'author_id');
    }

    public function transactions()
    {
        return $this->hasMany(MailTransaction::class);
    }

    public function lastTransaction()
    {
        return $this->hasOne(MailTransaction::class)->latestOfMany();
    }
    public function mailArchivings()
    {
        return $this->hasMany(MailArchiving::class, 'mail_id');
    }
}







