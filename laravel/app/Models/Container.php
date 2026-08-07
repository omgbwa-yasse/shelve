<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'shelve_id',
        'status_id',
        'property_id',
        'capacity_cm',
        'creator_id',
        'creator_organisation_id',
        'is_archived',
    ];

    protected $casts = [
        'capacity_cm' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'shelve_id');
    }

    public function status()
    {
        return $this->belongsTo(ContainerStatus::class, 'status_id');
    }

    public function property()
    {
        return $this->belongsTo(ContainerProperty::class, 'property_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function creatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'creator_organisation_id');
    }

    public function slipRecord()
    {
        return $this->belongsTo(SlipRecord::class);
    }

    /**
     * Mesure linéaire déjà occupée (somme des supports placés dans ce contenant).
     */
    public function usedLinearMeasureCm(): float
    {
        return (float) RecordMedium::where('container_id', $this->id)
            ->sum('linear_measure_cm');
    }

    /**
     * Espace linéaire restant dans le contenant (null si aucune capacité définie).
     */
    public function remainingSpaceCm(): ?float
    {
        if ($this->capacity_cm === null) {
            return null;
        }

        return round((float) $this->capacity_cm - $this->usedLinearMeasureCm(), 2);
    }

    public function mediums(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecordMedium::class, 'container_id');
    }

    public function records()
    {
        return $this->belongsToMany(RecordPhysical::class, 'record_physical_container', 'container_id', 'record_physical_id')
            ->withPivot(['description', 'creator_id']);
    }

    // Relation via shelf->room->organisations pour filtrer par organisation
    public function getOrganisationsAttribute()
    {
        return $this->shelf && $this->shelf->room ? $this->shelf->room->organisations : collect();
    }

    /**
     * Portée organisation (R03) : les conteneurs héritent de l'organisation de leur
     * rayonnage (salle), comme le fait l'index du contrôleur Blade.
     */
    public function scopeInOrganisation($query, int $organisationId)
    {
        return $query->whereHas('shelf.room.organisations', fn ($q) => $q->whereKey($organisationId));
    }
}
