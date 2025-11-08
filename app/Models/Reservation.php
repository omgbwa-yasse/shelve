<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ReservationStatus;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'content',
        'operator_id',
        'operator_organisation_id',
        'user_id',
        'user_organisation_id',
        'status',
        'return_date',
        'return_effective',
        'communication_id',
    ];

    protected $casts = [
        'status' => ReservationStatus::class,
        'return_date' => 'date',
        'return_effective' => 'date',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function records()
    {
        return $this->belongsToMany(RecordPhysical::class,'reservation_record','reservation_id');
    }

    public function userOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'user_organisation_id');
    }

    public function operatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'operator_organisation_id');
    }

    public function communication()
    {
        return $this->belongsTo(Communication::class, 'communication_id');
    }

    // Méthodes d'aide pour les statuts
    public function isPending(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::CANCELLED;
    }

    public function isInProgress(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === \App\Enums\ReservationStatus::COMPLETED;
    }
}
