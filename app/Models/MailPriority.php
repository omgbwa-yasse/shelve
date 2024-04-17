<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;


class MailPriority extends Model
{
    use HasFactory;
    protected $fillable = [
        'mail_priority_id'
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}
