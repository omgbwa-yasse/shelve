<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailPriority extends Model
{
    protected $fillable = [
        'mail_priority_id'
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}
