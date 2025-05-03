<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_responses';

    protected $fillable = [
        'document_request_id',
        'responded_by',
        'instructions',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function documentRequest()
    {
        return $this->belongsTo(PublicDocumentRequest::class, 'document_request_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function attachments()
    {
        return $this->hasMany(PublicResponseAttachment::class, 'public_response_id');
    }
}
