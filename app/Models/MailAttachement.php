<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MailAttachment extends Model
{
    use HasFactory;
    protected $fillable = ['path', 'filename', 'crypt', 'size'];
}
