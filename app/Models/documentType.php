<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_types';

    protected $fillable = [
        'name',
        'description',
    ];


    public function mails()
    {
        return $this->hasMany(Mail::class);
    }


    public function transactions()
    {
        return $this->hasMany(MailTransaction::class);
    }

    public function mailArchivings()
    {
        return $this->hasMany(MailArchiving::class, 'document_type_id');
    }

}
