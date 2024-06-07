<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;
use App\Models\Organisation;
use App\Models\User;
use App\Models\MailStatus;

class Transaction extends Model
{
    protected $fillable = [
        'code',
        'date_creation',
        'mail_id',
        'user_send',
        'organisation_send_id',
        'user_received',
        'organisation_received_id',
        'mail_status_id',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function userSend()
    {
        return $this->belongsTo(User::class, 'user_send');
    }

    public function organisationSend()
    {
        return $this->belongsTo(Organisation::class, 'organisation_send_id');
    }

    public function userReceived()
    {
        return $this->belongsTo(User::class, 'user_received');
    }

    public function organisationReceived()
    {
        return $this->belongsTo(Organisation::class, 'organisation_received_id');
    }

    public function mailStatus()
    {
        return $this->belongsTo(MailStatus::class, 'mail_status_id');
    }
}

