<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclassementList extends Model
{
    use HasFactory, BelongsToOrganisation;

    protected $fillable = [
        'code',
        'name',
        'description',
        'organisation_id',
        'declassement_status_id',
        'query_criteria',
        'creator_id',
        'is_approval_requested',
        'approval_requested_date',
        'approval_requested_by',
        'is_approved',
        'approved_date',
        'approved_by',
        'is_validated',
        'validated_date',
        'validated_by',
        'is_treated',
        'treated_date',
        'treated_by',
        'rejection_reason',
    ];

    protected $casts = [
        'query_criteria' => 'array',
        'is_approval_requested' => 'boolean',
        'is_approved' => 'boolean',
        'is_validated' => 'boolean',
        'is_treated' => 'boolean',
    ];

    public function status()
    {
        return $this->belongsTo(DeclassementStatus::class, 'declassement_status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function records()
    {
        return $this->hasMany(DeclassementRecord::class, 'declassement_list_id');
    }

    public function recordPhysicals()
    {
        return $this->belongsToMany(Record::class, 'declassement_records', 'declassement_list_id', 'record_id')
            ->withPivot(['added_by', 'comment']);
    }

    public function comments()
    {
        return $this->hasMany(DeclassementComment::class, 'declassement_list_id');
    }

    public function approvalRequestedBy()
    {
        return $this->belongsTo(User::class, 'approval_requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function treatedBy()
    {
        return $this->belongsTo(User::class, 'treated_by');
    }

    /**
     * Records dont le sort est "Élimination" et dont le délai de rétention est
     * écoulé, et qui ne figurent pas déjà dans une liste de déclassement non traitée.
     * Reproduit la requête de LifeCycleController::recordToEliminate().
     */
    public static function eligibleRecordsQuery(?int $activityId = null, ?int $organisationId = null)
    {
        $referenceDate = 'COALESCE(records.closing_date, records.end_date, records.date_exact, records.opening_date, records.start_date)';

        $query = Record::query()
            ->currentVersion()
            ->has('activity.retentions', '=', 1)
            ->join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->where('sorts.code', 'E')
            ->whereRaw("DATE_ADD({$referenceDate}, INTERVAL retentions.duration YEAR) < CURRENT_DATE")
            ->whereNull('records.destruction_effective_date')
            ->whereDoesntHave('declassementRecords', function ($q) {
                $q->whereHas('declassementList', function ($listQuery) {
                    $listQuery->where('is_treated', false);
                });
            })
            ->select('records.*')
            ->with(['activity.retentions.sort', 'status', 'level', 'creator']);

        if ($activityId) {
            $query->where('records.activity_id', $activityId);
        }

        if ($organisationId) {
            $query->where('records.organisation_id', $organisationId);
        }

        return $query;
    }
}
