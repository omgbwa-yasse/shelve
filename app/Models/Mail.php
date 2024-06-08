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
        'name',
        'authors',
        'description',
        'date',
        'create_by',
        'update_by',
        'subject_id',
        'mail_priority_id',
        'mail_type_id',
        'mail_typology_id',
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

    public function attachment()
    {
        return $this->belongsTo(MailAttachment::class);
    }

    public function send()
    {
        return $this->hasMany(Transaction::class);
    }

    public function received()
    {
        return $this->hasMany(Transaction::class);
    }

    public function container()
    {
        return $this->belongsToMany(MailContainer::class, 'mail_type_id',);
    }

    public function type()
    {
        return $this->belongsTo(MailType::class, 'mail_type_id');
    }

    public function subject()
    {
        return $this->belongsTo(MailSubject::class, 'subject_id', 'id');
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


}







