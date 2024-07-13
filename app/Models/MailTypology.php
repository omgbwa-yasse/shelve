<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TypologyCategory;
use App\Models\Mail;

class MailTypology extends Model
{


    protected $table = 'mail_typologies';


    protected $fillable = [
        'name',
        'description',
        'activity_id', // activity_id
    ];


    public $timestamps = false;

    public function class()
    {
        return $this->belongsTo(activity::class);
    }

    public function mails()
    {
        return $this->hasMany(mail::class);
    }
}

