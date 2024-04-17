<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAttachment;
use App\Models\MailContainer;
use App\Models\Transaction;

class Mail extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'object', 'description', 'authors', 'create_at', 'update_at', 'document_id', 'mail_priority_id', 'mail_typology_id'
    ];

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
}
