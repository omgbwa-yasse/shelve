<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicResponseAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_response_attachments';

    protected $fillable = [
        'public_response_id',
        'attachment_id',
        'download_count',
        'expires_at',
        'is_public',
        'uploaded_by',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'expires_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function response()
    {
        return $this->belongsTo(PublicResponse::class, 'public_response_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
