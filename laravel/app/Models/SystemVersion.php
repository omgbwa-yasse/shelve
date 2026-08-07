<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemVersion extends Model
{
    protected $fillable = [
        'version',
        'previous_version',
        'installed_at',
        'changelog',
        'installation_method',
        'is_rollback',
        'installed_by',
        'download_url',
        'checksum',
        'notes'
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'changelog' => 'array',
        'is_rollback' => 'boolean',
    ];

    public function installedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('installed_at', 'desc');
    }

    public function scopeStable($query)
    {
        return $query->where('installation_method', '!=', 'beta');
    }
}
