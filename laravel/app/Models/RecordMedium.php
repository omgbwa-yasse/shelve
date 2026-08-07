<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Support d'une notice (alignement SupportDocument IntelliGID).
 *
 * Une notice (records) possède 0, 1 ou plusieurs supports : placement physique
 * (container_id) et/ou fichier numérique (attachment_id) avec son état propre
 * (checkout, signature, statut draft/final/obsolete).
 */
class RecordMedium extends Model
{
    use HasFactory;

    protected $table = 'record_mediums';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    public const STATUS_OBSOLETE = 'obsolete';

    public const SIGNATURE_UNSIGNED = 'unsigned';

    public const SIGNATURE_SIGNED = 'signed';

    public const SIGNATURE_REJECTED = 'rejected';

    protected $fillable = [
        'record_id',
        'support_id',
        'container_id',
        'attachment_id',
        'status',
        'is_principal',
        'copy_code',
        'linear_measure_cm',
        'checked_out_by',
        'checked_out_at',
        'signature_status',
        'signed_by',
        'signed_at',
        'signature_data',
        'legacy_id',
    ];

    protected $casts = [
        'is_principal' => 'boolean',
        'linear_measure_cm' => 'decimal:2',
        'checked_out_at' => 'datetime',
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Versions mineure / majeure (étape 9).
     * DRAFT = version mineure ; FINAL = version majeure publiée ; OBSOLETE = archivée.
     */
    public function finalize(User $user): void
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new \Exception('Seul un support en état draft peut être finalisé.');
        }

        $this->update([
            'status' => self::STATUS_FINAL,
            'signed_by' => $user->id,
            'signed_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(RecordSupport::class, 'support_id');
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'container_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    public function checkedOutUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout / check-in
    |--------------------------------------------------------------------------
    */
    public function checkout(User $user): void
    {
        if ($this->isCheckedOut()) {
            throw new \Exception('Support is already checked out');
        }

        $this->update([
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
        ]);
    }

    public function checkin(User $user): void
    {
        if (! $this->isCheckedOut()) {
            throw new \Exception('Support is not checked out');
        }

        if ($this->checked_out_by !== $user->id) {
            throw new \Exception('Support is checked out by another user');
        }

        $this->cancelCheckout($user);
    }

    public function cancelCheckout(User $user): void
    {
        if (! $this->isCheckedOut()) {
            throw new \Exception('Support is not checked out');
        }

        if ($this->checked_out_by !== $user->id) {
            throw new \Exception('Support is checked out by another user');
        }

        $this->update([
            'checked_out_by' => null,
            'checked_out_at' => null,
        ]);
    }

    public function isCheckedOut(): bool
    {
        return ! is_null($this->checked_out_by);
    }

    public function isCheckedOutBy(User $user): bool
    {
        return $this->checked_out_by === $user->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */
    public function sign(User $user, ?string $signatureData = null): void
    {
        if ($this->signature_status === self::SIGNATURE_SIGNED) {
            throw new \Exception('Support is already signed');
        }

        if ($this->isCheckedOut()) {
            throw new \Exception('Cannot sign a checked out support');
        }

        $this->update([
            'signature_status' => self::SIGNATURE_SIGNED,
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_data' => $signatureData,
            'status' => self::STATUS_FINAL,
        ]);
    }

    public function rejectSignature(User $user, ?string $reason = null): void
    {
        $this->update([
            'signature_status' => self::SIGNATURE_REJECTED,
            'signature_data' => $reason,
        ]);
    }

    /**
     * Vérifie l'intégrité de la signature (présence des données et du signataire).
     */
    public function verifySignature(): bool
    {
        if ($this->signature_status !== self::SIGNATURE_SIGNED) {
            return false;
        }

        return ! empty($this->signature_data) && $this->signed_by !== null;
    }

    public function revokeSignature(User $user): void
    {
        if ($this->signature_status !== self::SIGNATURE_SIGNED) {
            throw new \Exception('Support is not signed');
        }

        $this->update([
            'signature_status' => self::SIGNATURE_UNSIGNED,
            'signed_by' => null,
            'signed_at' => null,
            'signature_data' => null,
            'status' => self::STATUS_DRAFT,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeDigital($query)
    {
        return $query->whereHas('support', fn ($q) => $q->whereRaw('LOWER(name) = ?', ['numérique']));
    }

    public function scopePhysical($query)
    {
        return $query->whereHas('support', fn ($q) => $q->whereRaw('LOWER(name) = ?', ['papier']));
    }

    public function scopePrincipal($query)
    {
        return $query->where('is_principal', true);
    }
}
