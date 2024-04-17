<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailbatchTransaction extends Model
{
    protected $fillable = ['mailbatch_id', 'mail_id', 'organisation_send_id', 'organisation_received_id', 'create_at', 'update_at'];

    public function mailbatch()
    {
        return $this->belongsTo(Mailbatch::class);
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function organisationSend()
    {
        return $this->belongsTo(Organisation::class, 'organisation_send_id');
    }

    public function organisationReceived()
    {
        return $this->belongsTo(Organisation::class, 'organisation_received_id');
    }
}
