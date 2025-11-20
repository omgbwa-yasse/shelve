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
        'creator_id',
        'creator_organisation_id',
        'is_archived'
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
        return $this->belongsTo(SlipRecord::class,);
    }

    public function records()
    {
        return $this->belongsToMany(RecordPhysical::class, 'record_physical_container', 'container_id', 'record_id')
            ->withPivot(['description', 'creator_id']);
    }

    // Relation via shelf->room->organisations pour filtrer par organisation
    public function getOrganisationsAttribute()
    {
        return $this->shelf && $this->shelf->room ? $this->shelf->room->organisations : collect();
    }
}
