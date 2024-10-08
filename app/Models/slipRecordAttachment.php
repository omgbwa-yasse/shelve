<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipRecordAttachment extends Model
{
    use HasFactory;
    protected $table = 'slip_record_attachments';

    protected $fillable = [
        'slip_record_id',
        'attachment_id',
    ];

    public function slipRecord()
    {
        return $this->belongsTo(SlipRecord::class, 'slip_record_id');
    }




}
