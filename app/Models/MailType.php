<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;

class MailType extends Model
{
    use HasFactory;

    protected $table = 'mail_types';


    protected $fillable = ['name'];


    public $timestamps = false;


    public function Mails()
    {
        return $this->hasMany(Mail::class);
    }

    public function emails()
    {
        return $this->hasMany(Mail::class);
    }
}
