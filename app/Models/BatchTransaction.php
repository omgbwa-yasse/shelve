<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'organisation_send_id',
        'organisation_received_id'
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function organisationSend()
    {
        return $this->belongsTo(Organisation::class, 'organisation_send_id');
    }

    public function organisationReceived()
    {
        return $this->belongsTo(Organisation::class, 'organisation_received_id');
    }

}
