<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailAttachment extends Model
{

    use HasFactory;

    protected $fillable = [
        'path',
        'name',
        'crypt',
        'size',
        'creator_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'mail_attachment', 'attachment_id', 'mail_id');
    }

}
