<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_records';

    protected $fillable = [
        'record_id',
        'published_at',
        'expires_at',
        'published_by',
        'publication_notes',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function documentRequests()
    {
        return $this->hasMany(PublicDocumentRequest::class, 'record_id');
    }
}
