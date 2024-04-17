<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailAttachment extends Model
{
    protected $fillable = [
        'id'
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}
