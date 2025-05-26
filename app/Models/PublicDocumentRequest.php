<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicDocumentRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_document_requests';

    protected $fillable = [
        'user_id',
        'record_id',
        'request_type',
        'reason',
        'status',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }

    public function record()
    {
        return $this->belongsTo(PublicRecord::class, 'record_id');
    }

    public function responses()
    {
        return $this->hasMany(PublicResponse::class, 'document_request_id');
    }
}
