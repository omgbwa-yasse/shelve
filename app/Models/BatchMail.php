<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'mail_id',
        'insertion_date',
        'exit_date'
    ];

    public function batch()
    {
        return $this->belongsTo(MailBatch::class);
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

}
