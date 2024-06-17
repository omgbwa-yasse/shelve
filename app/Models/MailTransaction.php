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
        'mail_status_id',
        'document_type_id',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function scopeLatestOfMany($query)
    {
        return $query->orderByDesc('created_at')->limit(1);
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

    public function mailStatus()
    {
        return $this->belongsTo(MailStatus::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}

