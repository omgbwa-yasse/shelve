<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicRecordAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_record_attachments';

    protected $fillable = [
        'public_record_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function publicRecord()
    {
        return $this->belongsTo(PublicRecord::class, 'public_record_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
