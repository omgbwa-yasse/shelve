<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTypology extends Model
{
    use HasFactory;

    protected $table = 'mail_typologies';

    protected $fillable = [
        'code',
        'name',
        'description',
        'activity_id',
    ];

    public $timestamps = true;

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function mails()
    {
        return $this->hasMany(Mail::class, 'typology_id');
    }
}
