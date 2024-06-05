<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;

class MailSubject extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
    public $timestamps = true;
}

