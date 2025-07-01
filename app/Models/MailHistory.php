<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Enums\MailStatusEnum;

class MailHistory extends Model
{
    use HasFactory;

    protected $table = 'mail_histories';

    protected $fillable = [
        'mail_id',
        'user_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'description',
        'ip_address',
        'user_agent',
        'location_data',
        'processing_time',
        'metadata'
    ];

    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
        'location_data' => 'json',
        'metadata' => 'json',
        'processing_time' => 'integer', // en secondes
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMail($query, $mailId)
    {
        return $query->where('mail_id', $mailId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('field_changed', 'status');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getFormattedProcessingTimeAttribute()
    {
        if (!$this->processing_time) {
            return null;
        }

        $seconds = $this->processing_time;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'j';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($remainingSeconds > 0 || empty($parts)) {
            $parts[] = $remainingSeconds . 's';
        }

        return implode(' ', $parts);
    }

    public static function logAction($mailId, $action, $fieldChanged = null, $oldValue = null, $newValue = null, $description = null)
    {
        return static::create([
            'mail_id' => $mailId,
            'user_id' => Auth::check() ? Auth::id() : null,
            'action' => $action,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location_data' => [
                'organisation_id' => Auth::check() ? Auth::user()->current_organisation_id ?? null : null,
                'session_id' => session()->getId(),
            ]
        ]);
    }

    // Méthodes utilitaires pour l'affichage
    public function getActionLabel()
    {
        return match($this->action) {
            'created' => 'Créé',
            'updated' => 'Modifié',
            'status_changed' => 'Statut changé',
            'assigned' => 'Assigné',
            'unassigned' => 'Désassigné',
            'deadline_set' => 'Échéance définie',
            'deadline_updated' => 'Échéance modifiée',
            'commented' => 'Commenté',
            'archived' => 'Archivé',
            'restored' => 'Restauré',
            'deleted' => 'Supprimé',
            default => ucfirst($this->action)
        };
    }

    public function getActionBadgeClass()
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'status_changed' => 'warning',
            'assigned' => 'primary',
            'unassigned' => 'secondary',
            'deadline_set' => 'info',
            'deadline_updated' => 'warning',
            'commented' => 'light',
            'archived' => 'dark',
            'restored' => 'success',
            'deleted' => 'danger',
            default => 'secondary'
        };
    }
}
