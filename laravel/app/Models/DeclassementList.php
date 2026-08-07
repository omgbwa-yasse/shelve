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
    public static function eligibleRecordsQuery(?int $activityId = null)
    {
        $referenceDate = "COALESCE(
            CASE
                WHEN record_physicals.date_format = 'Y' AND record_physicals.date_end REGEXP '^[0-9]{4}$' THEN MAKEDATE(record_physicals.date_end, 365)
                WHEN record_physicals.date_format = 'M' AND record_physicals.date_end REGEXP '^[0-9]{4}/[0-9]{1,2}$' THEN STR_TO_DATE(CONCAT(REPLACE(record_physicals.date_end, '/', '-'), '-01'), '%Y-%m-%d')
                WHEN record_physicals.date_format = 'D' AND record_physicals.date_end REGEXP '^[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}$' THEN STR_TO_DATE(REPLACE(record_physicals.date_end, '/', '-'), '%Y-%m-%d')
                ELSE NULL
            END,
            record_physicals.date_exact
        )";

        $query = RecordPhysical::query()
            ->join('activities', 'record_physicals.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->where('sorts.code', 'E')
            ->whereRaw("DATEDIFF(NOW(), {$referenceDate}) > retentions.duration * 365")
            ->whereDoesntHave('declassementRecords', function ($q) {
                $q->whereHas('declassementList', function ($listQuery) {
                    $listQuery->where('is_treated', false);
                });
            })
            ->select('record_physicals.*')
            ->with(['activity', 'status', 'level', 'user']);

        if ($activityId) {
            $query->where('record_physicals.activity_id', $activityId);
        }

        return $query;
    }
}
