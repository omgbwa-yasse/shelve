<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\MailSubject;
use App\Models\MailBatch;
use App\Models\MailAttachment;
use App\Models\MailContainer;
use App\Models\Transaction;

class Mail extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'object',
        'date',
        'description',
        'subject_id',
        'type_id',
        'authors',
        'document_id',
        'mail_priority_id',
        'mail_typology_id'
    ];

    public $timestamps = true;

    public function mailPriority()
    {
        return $this->belongsTo(MailPriority::class);
    }

    public function mailTypology()
    {
        return $this->belongsTo(MailTypology::class);
    }

    public function mailAttachment()
    {
        return $this->belongsTo(MailAttachment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function mailContainers()
    {
        return $this->belongsToMany(MailContainer::class);
    }

    public function mailType()
    {
        return $this->belongsTo(MailType::class);
    }

    public function mailSubject()
    {
        return $this->belongsTo(MailSubject::class);
    }
    public function mailBatch()
    {
        return $this->belongsTo(MailBatch::class);
    }


}
