<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class slipAttachment extends Model
{
    use HasFactory;

    protected $table = 'slip_attachments';

    protected $fillable = [
        'slip_id',
        'attachment_id',
    ];

    public function slip()
    {
        return $this->belongsTo(Slip::class, 'slip_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }


}
