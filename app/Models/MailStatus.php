<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class MailStatus extends Model
{
    use HasFactory;
    protected $table = 'mail_statuses';
    protected $fillable = [
        'name'
    ];

    public function transactions()
    {
        return $this->hasMany(MailTransaction::class);
    }
}
