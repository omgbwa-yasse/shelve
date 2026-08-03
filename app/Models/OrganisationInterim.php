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
        'activity_id',
        'scope',
        'is_primary',
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
        'is_primary' => 'boolean',
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
     * Activité du plan de classement déléguée à l'intérimaire (facultatif).
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
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
     * Tous les intérims actifs d'un titulaire pour une organisation.
     * Un titulaire peut être remplacé par plusieurs intérimaires en parallèle,
     * chacun sur son volet ; le principal est renvoyé en premier.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function activeForAll(int $titularUserId, int $organisationId)
    {
        return static::currentlyActive()
            ->where('titular_user_id', $titularUserId)
            ->where('organisation_id', $organisationId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();
    }

    /**
     * Retourne l'intérim actif principal remplaçant un titulaire donné pour une
     * organisation (celui qui reçoit le courrier routé par défaut), ou null.
     */
    public static function activeFor(int $titularUserId, int $organisationId): ?self
    {
        return static::activeForAll($titularUserId, $organisationId)->first();
    }

    /**
     * Intérim actif compétent pour une activité donnée du plan de classement.
     * Ordre de préférence : le volet qui porte exactement cette activité, sinon
     * un intérimaire sans activité (toutes attributions), sinon le principal.
     */
    public static function activeForActivity(int $titularUserId, int $organisationId, ?int $activityId): ?self
    {
        $volets = static::activeForAll($titularUserId, $organisationId);

        if ($volets->isEmpty()) {
            return null;
        }

        if ($activityId) {
            $exact = $volets->firstWhere('activity_id', $activityId);
            if ($exact) {
                return $exact;
            }
        }

        return $volets->firstWhere('activity_id', null) ?? $volets->first();
    }

    /**
     * Volets actuellement délégués à un utilisateur (en tant qu'intérimaire),
     * éventuellement restreints à une organisation.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function activeVoletsOf(int $interimUserId, ?int $organisationId = null)
    {
        return static::currentlyActive()
            ->where('interim_user_id', $interimUserId)
            ->when($organisationId, fn ($q) => $q->where('organisation_id', $organisationId))
            ->get();
    }

    /**
     * Ce volet couvre-t-il l'activité donnée ?
     * Un volet sans activité couvre toutes les attributions du titulaire.
     */
    public function coversActivity(?int $activityId): bool
    {
        return $this->activity_id === null || (int) $this->activity_id === (int) $activityId;
    }

    /**
     * Libellé du volet géré par cet intérimaire.
     */
    public function voletLabel(): string
    {
        $activity = $this->activity?->name;

        if ($activity && $this->scope) {
            return $activity . ' — ' . $this->scope;
        }

        return $activity ?: ($this->scope ?: 'Toutes les attributions');
    }
}
