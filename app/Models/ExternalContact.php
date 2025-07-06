<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalContact extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'position',
        'external_organization_id',
        'is_primary_contact',
        'is_verified',
        'notes'
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the full name of the contact
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the organization this contact belongs to
     */
    public function organization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_organization_id');
    }

    /**
     * Get the mails sent by this contact
     */
    public function sentMails()
    {
        // Column doesn't exist yet - using empty query for now
        return $this->hasOne(Mail::class)->whereRaw('1 = 0');
    }

    /**
     * Get the mails received by this contact
     */
    public function receivedMails()
    {
        // Column doesn't exist yet - using empty query for now
        return $this->hasOne(Mail::class)->whereRaw('1 = 0');
    }
}
