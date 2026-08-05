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
        'archived_by',
        'document_type',
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

    /**
     * Portée organisation (R03) : une archive de courrier appartient à l'organisation qui
     * possède le contenant (`mail_containers.creator_organisation_id`) — l'archive n'a pas
     * de colonne d'organisation propre, le contenant fait foi.
     */
    public function scopeInOrganisation($query, int $organisationId)
    {
        return $query->whereHas('container', fn ($q) => $q->where('creator_organisation_id', $organisationId));
    }


}
