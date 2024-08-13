<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailAction extends Model
{
    use HasFactory;


    protected $table = 'mail_actions';

    protected $fillable = [
        'name',
        'description',
    ];


    public function transaction()
    {
        return $this->hasMany(MailTransaction::class, 'action_id');
    }

}
