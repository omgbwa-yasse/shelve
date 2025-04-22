<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailArchive extends Model
{
    use HasFactory;

    protected $table = 'mail_archives';

    protected $fillable = [
        'container_id',
        'mail_id',
        'archived_by', // Ajouté d'après le schéma SQL
        'document_type', // Corrigé d'après le schéma SQL
    ];

    public function container()
    {
        return $this->belongsTo(MailContainer::class, 'container_id');
    }



    public function mail()
    {
        return $this->belongsTo(Mail::class,  'mail_id' ); 
    }


    
    public function user() // Renommé pour plus de clarté
    {
        return $this->belongsTo(User::class, 'archived_by');
    }



}
