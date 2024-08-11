<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyMail extends Model
{
    use HasFactory;


    protected $table = 'dolly_mails';

    protected $fillable = [
        'mail_id',
        'dolly_id',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}
