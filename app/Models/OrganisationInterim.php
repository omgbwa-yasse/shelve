<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrganisationInterim extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'titular_user_id',
        'interim_user_id',
        'start_date',
        'end_date',
        'is_active',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function titular()
    {
        return $this->belongsTo(User::class, 'titular_user_id');
    }

    public function interim()
    {
        return $this->belongsTo(User::class, 'interim_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope : intérims actifs à la date du jour (drapeau actif + dans la période).
     */
    public function scopeCurrentlyActive($query)
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            });
    }

    /**
     * Retourne l'intérim actif remplaçant un titulaire donné pour une organisation,
     * ou null s'il n'y en a pas.
     */
    public static function activeFor(int $titularUserId, int $organisationId): ?self
    {
        return static::currentlyActive()
            ->where('titular_user_id', $titularUserId)
            ->where('organisation_id', $organisationId)
            ->first();
    }
}
