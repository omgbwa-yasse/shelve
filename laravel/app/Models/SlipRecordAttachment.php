<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipRecordAttachment extends Model
{
    use HasFactory;
    protected $table = 'slip_record_attachments';

    // La pivot n'a pas de colonne `id` : clé composite `(slip_record_id, attachment_id)`.
    public $incrementing = false;

    protected $primaryKey = [
        'slip_record_id',
        'attachment_id',
    ];

    protected $keyType = 'array';

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
