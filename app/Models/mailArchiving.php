<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailArchiving extends Model
{
    use HasFactory;

    protected $table = 'mail_archiving';

    protected $fillable = [
        'container_id',
        'mail_id',
        'document_type_id',
    ];

    public function container()
    {
        return $this->belongsTo(MailContainer::class, 'container_id');
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}


