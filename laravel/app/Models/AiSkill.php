<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'version',
        'location',
        'folder',
        'enabled',
        'installed_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    public function getBasePathAttribute(): string
    {
        $root = $this->location === 'system'
            ? storage_path('app/ai/skills/system')
            : storage_path('app/ai/skills/custom');

        return $this->folder ? rtrim($root, '/\\') . DIRECTORY_SEPARATOR . $this->folder : $root;
    }

    public function getSkillMdPathAttribute(): string
    {
        return $this->base_path . DIRECTORY_SEPARATOR . 'SKILL.md';
    }
}
