<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Cotation d'un courrier entrant à une direction donnée. Un courrier peut être
 * coté à plusieurs directions : chacune a sa propre ligne, son instruction, son
 * affectation et son statut de réception — ce qui permet au DG de suivre la
 * réponse de chaque direction individuellement.
 */
class MailCotation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'mail_id',
        'organisation_id',
        'action_id',
        'activity_id',
        'instruction',
        'assigned_to',
        'status',
        'confirmed_at',
        'coted_by',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function action()
    {
        return $this->belongsTo(MailAction::class, 'action_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Activité du plan de classement au titre de laquelle la direction traite
     * ce courrier.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Cotations dont un utilisateur a la charge : celles de sa direction, plus
     * celles des directions où il assure un intérim — restreintes, dans ce cas,
     * à l'activité de son volet. Un intérimaire ne voit que son périmètre.
     */
    public function scopeHandledBy($query, User $user)
    {
        $volets = OrganisationInterim::activeVoletsOf($user->id);

        return $query->where(function ($q) use ($user, $volets) {
            $q->where('organisation_id', $user->current_organisation_id);

            foreach ($volets as $volet) {
                $q->orWhere(function ($q) use ($volet) {
                    $q->where('organisation_id', $volet->organisation_id);

                    // Volet sans activité = toutes les attributions du titulaire.
                    if (!$volet->activity_id) {
                        return;
                    }

                    $activityId = (int) $volet->activity_id;

                    $q->where(function ($q) use ($activityId) {
                        $q->where('activity_id', $activityId)
                            // Cotation sans activité : on retombe sur celle du courrier.
                            ->orWhere(function ($q) use ($activityId) {
                                $q->whereNull('activity_id')
                                    ->whereHas('mail', function ($m) use ($activityId) {
                                        $m->where('activity_id', $activityId)->orWhereNull('activity_id');
                                    });
                            });
                    });
                });
            }
        });
    }
}
