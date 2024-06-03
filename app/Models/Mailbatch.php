<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;

class Mailbatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = false;

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}
