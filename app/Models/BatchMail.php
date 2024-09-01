<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchMail extends Model
{
    use HasFactory;

    protected $table = 'batch_mail';

    protected $fillable = [
        'batch_id',
        'mail_id',
        'insert_date',
        'remove_date',
    ];

    protected $dates = [
        'insert_date',
        'remove_date',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }
}
