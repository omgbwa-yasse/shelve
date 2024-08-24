<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordAttachment extends Model
{
protected $table="record_attachment";
    use HasFactory;

    protected $fillable = [
        'path',
        'name',
        'crypt',
        'size',
        'creator_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function records()
    {
        return $this->belongsToMany(record::class, 'record_attachment', 'attachment_id', 'mail_id');
    }

}
