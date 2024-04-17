<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;

class MailAttachment extends Model
{
    use HasFactory;
    protected $fillable = [
        'id'
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}
