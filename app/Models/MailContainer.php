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
        'property_id',
        'created_by',
        'creator_organisation_id'
    ];

    protected $table = 'mail_containers';

    public function containerProperty()
    {
        return $this->belongsTo(ContainerProperty::class, 'property_id');
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

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'creator_organisation_id');
    }

    /**
     * Portée organisation (R03) : un contenant de courrier appartient à l'organisation
     * qui l'a créé (`creator_organisation_id`), comme le filtre l'index du contrôleur
     * Blade (`MailContainerController::index()`).
     */
    public function scopeInOrganisation($query, int $organisationId)
    {
        return $query->where('creator_organisation_id', $organisationId);
    }

    public $timestamps = true;
}
