<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'date_creation',
        'mail_id',
        'user_send_id',
        'organisation_send_id',
        'user_received_id',
        'organisation_received_id',
        'mail_type_id',
        'document_type_id',
        'action_id',
        'to_return',
        'description',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }
    public function type()
    {
        return $this->belongsTo(MailType::class, 'mail_type_id');
    }
    public function userSend()
    {
        return $this->belongsTo(User::class, 'user_send_id');
    }

    public function organisationSend()
    {
        return $this->belongsTo(Organisation::class, 'organisation_send_id');
    }

    public function userReceived()
    {
        return $this->belongsTo(User::class, 'user_received_id');
    }

    public function organisationReceived()
    {
        return $this->belongsTo(Organisation::class, 'organisation_received_id');
    }

    public function mailType()
    {
        return $this->belongsTo(MailType::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function action()
    {
        return $this->belongsTo(MailAction::class);
    }
}

