<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailAttachment extends Model
{
    use HasFactory;

    protected $table = "attachments";

    protected $fillable = [
        'path',
        'name',
        'crypt',
        'size',
        'creator_id',
        'crypt_sha512',
        'thumbnail_path',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'mail_attachment', 'attachment_id', 'mail_id')
                    ->withPivot('added_by') // Ajouté pour correspondre au schéma SQL
                    ->withTimestamps();
    }
}
