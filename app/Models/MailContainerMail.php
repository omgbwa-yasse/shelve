<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MailContainerMail extends Pivot
{
    use HasFactory;
    protected $table = 'mail_container';
}
