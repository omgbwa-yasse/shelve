<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalOrganization extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'name',
        'legal_form',
        'registration_number',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
        'is_verified',
        'notes'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    /**
     * Get the contacts associated with this organization
     */
    public function contacts()
    {
        return $this->hasMany(ExternalContact::class, 'external_organization_id');
    }

    /**
     * Get the primary contact for this organization
     */
    public function primaryContact()
    {
        return $this->hasOne(ExternalContact::class, 'external_organization_id')
                    ->where('is_primary_contact', true);
    }

    /**
     * Get the mails sent by this organization
     */
    public function sentMails()
    {
        return $this->hasMany(Mail::class, 'external_sender_organization_id');
    }

    /**
     * Get the mails received by this organization
     */
    public function receivedMails()
    {
        return $this->hasMany(Mail::class, 'external_recipient_organization_id');
    }
}
