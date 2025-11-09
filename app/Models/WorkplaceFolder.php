<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkplaceFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'workplace_id',
        'folder_id',
        'shared_by',
        'shared_at',
        'share_note',
        'access_level',
        'is_pinned',
        'display_order',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'is_pinned' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'folder_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    /**
     * Scopes
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true)->orderBy('display_order');
    }

    public function scopeByAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }
}
