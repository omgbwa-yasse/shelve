<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;

class MailSubject extends Model
{
    protected $fillable = ['name'];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}

