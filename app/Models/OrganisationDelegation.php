<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegating_organisation_id',
        'delegate_organisation_id',
        'delegated_by_user_id',
        'start_date',
        'end_date',
        'is_active',
        'scope',
        'permissions',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * L'organisation qui délègue
     */
    public function delegatingOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'delegating_organisation_id');
    }

    /**
     * L'organisation déléguée
     */
    public function delegateOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'delegate_organisation_id');
    }

    /**
     * L'utilisateur qui a créé la délégation
     */
    public function delegatedBy()
    {
        return $this->belongsTo(User::class, 'delegated_by_user_id');
    }

    /**
     * Scope pour les délégations actives
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope pour les délégations expirant prochainement
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Vérifier si la délégation est active actuellement
     */
    public function isCurrentlyActive()
    {
        return $this->is_active &&
              ($this->end_date === null || $this->end_date >= now());
    }

    /**
     * Désactiver la délégation
     */
    public function deactivate()
    {
        $this->is_active = false;
        $this->save();

        return $this;
    }
}
