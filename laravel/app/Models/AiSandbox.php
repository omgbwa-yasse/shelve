<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sandbox d'exécution Python pour l'assistant IA (D14).
 *
 * Un sandbox = un workspace sur disque structuré selon un "pattern"
 * (ex. `standard` : input/ core/ reference/ output/ logs/), rattaché
 * facultativement à une conversation AI.
 */
class AiSandbox extends Model
{
    use HasFactory, BelongsToOrganisation;

    public const PATTERN_STANDARD = 'standard';

    public const ENGINE_LOCAL = 'local';
    public const ENGINE_DOCKER = 'docker';

    public const STATUS_CREATED = 'created';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_RUNNING,
        self::STATUS_SUCCESS,
        self::STATUS_ERROR,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'organisation_id',
        'user_id',
        'conversation_id',
        'name',
        'pattern',
        'engine',
        'status',
        'folder',
        'last_output',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AiSandboxFile::class, 'sandbox_id');
    }

    public function outputFiles(): HasMany
    {
        return $this->hasMany(AiSandboxFile::class, 'sandbox_id')->where('section', 'output');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
