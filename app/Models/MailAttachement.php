<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailAttachment extends Model
{
    protected $fillable = ['path', 'filename', 'crypt', 'size'];
}
