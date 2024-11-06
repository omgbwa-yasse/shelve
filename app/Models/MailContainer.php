<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MailContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type_id',
        'created_by', // Corrigé d'après le schéma SQL
        'creator_organisation_id'
    ];

    protected $table = 'mail_containers';

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class, 'type_id');
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'mail_archives', 'container_id', 'mail_id') // Table pivot spécifiée
                    ->withPivot('archived_by', 'document_type') // Champs pivot ajoutés
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by'); // Clé étrangère corrigée
    }

    public function creatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'creator_organisation_id');
    }

    public $timestamps = true;
}
