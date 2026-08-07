<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Métadonnée d'un fichier présent dans le workspace d'un sandbox.
 *
 * Les binaires ne sont jamais stockés en base : `path` est relatif au
 * workspace du sandbox (`storage/app/ai/sandboxes/{folder}/{section}/...`).
 */
class AiSandboxFile extends Model
{
    use HasFactory;

    public const SECTION_INPUT = 'input';
    public const SECTION_CORE = 'core';
    public const SECTION_REFERENCE = 'reference';
    public const SECTION_OUTPUT = 'output';
    public const SECTION_LOGS = 'logs';

    public const SECTIONS = [
        self::SECTION_INPUT,
        self::SECTION_CORE,
        self::SECTION_REFERENCE,
        self::SECTION_OUTPUT,
        self::SECTION_LOGS,
    ];

    protected $table = 'ai_sandbox_files';

    public $timestamps = false;

    protected $fillable = [
        'sandbox_id',
        'section',
        'path',
        'name',
        'size',
        'mime',
        'hash',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $file) {
            $file->created_at ??= now();
        });
    }

    public function sandbox(): BelongsTo
    {
        return $this->belongsTo(AiSandbox::class, 'sandbox_id');
    }
}
