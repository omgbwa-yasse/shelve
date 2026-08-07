<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThesaurusOrganization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'homepage',
        'email',
    ];

    /**
     * Relation avec les schémas
     */
    public function schemes(): BelongsToMany
    {
        return $this->belongsToMany(ThesaurusScheme::class, 'thesaurus_scheme_organizations', 'organization_id', 'scheme_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->whereHas('schemes', function ($query) use ($role) {
            $query->wherePivot('role', $role);
        });
    }

    /**
     * Scope pour les créateurs
     */
    public function scopeCreators($query)
    {
        return $query->byRole('creator');
    }

    /**
     * Scope pour les contributeurs
     */
    public function scopeContributors($query)
    {
        return $query->byRole('contributor');
    }

    /**
     * Scope pour les éditeurs
     */
    public function scopePublishers($query)
    {
        return $query->byRole('publisher');
    }

    /**
     * Scope pour les mainteneurs
     */
    public function scopeMaintainers($query)
    {
        return $query->byRole('maintainer');
    }
}
