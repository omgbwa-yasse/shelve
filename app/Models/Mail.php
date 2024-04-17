<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
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
