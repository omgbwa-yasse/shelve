<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyMailTransaction extends Model
{
    use HasFactory;

    protected $table = 'dolly_mail_transactions';

    protected $fillable = [
        'mail_transaction_id',
        'dolly_id',
    ];


    public function mailTransaction()
    {
        return $this->belongsTo(MailTransaction::class, 'mail_transaction_id');
    }


    public function dolly()
    {
        return $this->belongsTo(Dolly::class, 'dolly_id');
    }
}
