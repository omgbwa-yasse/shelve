<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Container;
use App\Models\Organisation;
use App\Models\RecordStatus;
use App\Models\RecordLevel;
use App\Models\RecordAttachment;
use App\Models\RecordSupport;
use App\Models\Classification;
use App\Models\User;

class Record extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'code',
        'name',
        'date_format',
        'date_start',
        'date_end',
        'date_exact',
        'level_id',
        'width',
        'width_description',
        'biographical_history',
        'archival_history',
        'acquisition_source',
        'content',
        'appraisal',
        'accrual',
        'arrangement',
        'access_conditions',
        'reproduction_conditions',
        'language_material',
        'characteristic',
        'finding_aids',
        'location_original',
        'location_copy',
        'related_unit',
        'publication_note',
        'note',
        'archivist_note',
        'rule_convention',
        'status_id',
        'support_id',
        'activity_id',
        'parent_id',
        'container_id',
        'user_id',
        'organisation_id'
    ];


    // Relation avec Container via la table pivot
    public function containers()
    {
        return $this->belongsToMany(Container::class, 'record_container')
            ->withPivot(['description', 'creator_id']);
    }

    // Relation avec RecordContainer
    public function recordContainers()
    {
        return $this->hasMany(RecordContainer::class);
    }

    // Relations pour accéder aux Shelf et Room via Container
    public function shelves()
    {
        return $this->hasManyThrough(
            Shelf::class,
            Container::class,
            'id', // Clé étrangère sur containers
            'id', // Clé primaire sur shelves
            'id', // Clé locale sur records
            'shelve_id' // Clé locale sur containers
        );
    }

    public function rooms()
    {
        return $this->hasManyThrough(
            Room::class,
            Shelf::class,
            'id',
            'id',
            'id',
            'room_id'
        );
    }

    // Autres relations existantes...
    public function status()
    {
        return $this->belongsTo(RecordStatus::class, 'status_id');
    }

    public function support()
    {
        return $this->belongsTo(RecordSupport::class, 'support_id');
    }

    public function level()
    {
        return $this->belongsTo(RecordLevel::class, 'level_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function ownerOrganisation()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_activity', 'activity_id', 'organisation_id')
                ->withPivot('activity_id')
                ->whereHas('activities', function($query) {
                    $query->where('activities.id', $this->activity_id);
                });
    }

    public function parent()
    {
        return $this->belongsTo(Record::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Record::class, 'parent_id');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'record_author');
    }

    public function terms()
    {
        return $this->belongsToMany(Term::class);
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'record_attachment');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'date_exact' => $this->date_exact,
            'biographical_history' => $this->biographical_history,
            'archival_history' => $this->archival_history,
            'acquisition_source' => $this->acquisition_source,
            'content' => $this->content,
            'appraisal' => $this->appraisal,
            'accrual' => $this->accrual,
            'arrangement' => $this->arrangement,
            'access_conditions' => $this->access_conditions,
            'reproduction_conditions' => $this->reproduction_conditions,
            'language_material' => $this->language_material,
            'characteristic' => $this->characteristic,
            'finding_aids' => $this->finding_aids,
            'location_original' => $this->location_original,
            'location_copy' => $this->location_copy,
            'related_unit' => $this->related_unit,
            'publication_note' => $this->publication_note,
            'note' => $this->note,
            'archivist_note' => $this->archivist_note,
            'rule_convention' => $this->rule_convention,
        ];
    }

}
