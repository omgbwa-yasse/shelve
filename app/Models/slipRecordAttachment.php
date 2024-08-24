<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class slipRecordAttachment extends Model
{
    use HasFactory;
    protected $table = 'slip_record_attachment';

    protected $fillable = [
        'slip_record_id',
        'attachment_id',
    ];

    public function slipRecord()
    {
        return $this->belongsTo(SlipRecord::class, 'slip_record_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }


}
