<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'observation',
        'parent_id',
        'communicability_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }

    public function retentions()
    {
        return $this->belongsToMany(Retention::class, 'retention_activity', 'activity_id', 'retention_id');
    }


    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_activity',  'activity_id', 'organisation_id');
    }

    public function communicability()
    {
        return $this->belongsTo(Communicability::class, 'communicability_id');
    }

    public function mailTypologies()
    {
        return $this->hasMany(MailTypology::class, 'activity_id');
    }

    public function records()
    {
        return $this->hasMany(Record::class, 'activity_id');
    }
}
