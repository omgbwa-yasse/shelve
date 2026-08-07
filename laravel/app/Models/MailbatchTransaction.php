<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\batchMail;
use App\Models\Organisation;

class MailbatchTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['mailbatch_id', 'mail_id', 'organisation_send_id', 'organisation_received_id', 'create_at', 'update_at'];

    public function mailbatch()
    {
        return $this->belongsTo(batchMail::class);
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
