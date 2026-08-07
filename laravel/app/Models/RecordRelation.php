<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordRelation extends Model
{
    use HasFactory;

    protected $table = 'record_relations';

    public const TYPE_VERSION_OF = 'version_of';
    public const TYPE_REPLACES = 'replaces';
    public const TYPE_REFERS_TO = 'refers_to';
    public const TYPE_REQUIRES = 'requires';
    public const TYPE_HAS_PART = 'has_part';
    public const TYPE_CONFORMS_TO = 'conforms_to';

    protected $fillable = [
        'source_id',
        'target_id',
        'type',
    ];

    /**
     * Notice source (celle qui porte la relation).
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'source_id');
    }

    /**
     * Notice cible.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'target_id');
    }

    /**
     * Filtrer les relations par type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
