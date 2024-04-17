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
    use HasFactory;
    protected $fillable = [
        'reference', 'date_creation', 'mail_id', 'user_send', 'organisation_send_id', 'user_receveid', 'organisation_received_id', 'mail_status_id', 'create_at', 'update_at'
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

    public function userReceive()
    {
        return $this->belongsTo(User::class, 'user_receveid');
    }

    public function organisationReceived()
    {
        return $this->belongsTo(Organisation::class, 'organisation_received_id');
    }

    public function mailStatus()
    {
        return $this->belongsTo(MailStatus::class);
    }
}
