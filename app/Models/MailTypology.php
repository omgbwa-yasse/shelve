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
        'class_id',
    ];


    public function class()
    {
        return $this->belongsTo(activity::class,'class_id');
    }
}

