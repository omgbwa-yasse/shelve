<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVersion extends Model
{
    protected $fillable = [
        'template_id',
        'version',
        'layout',
        'custom_css',
        'custom_js',
        'variables',
        'components',
        'meta',
        'created_by',
        'change_description',
        'is_active'
    ];

    protected $casts = [
        'variables' => 'array',
        'components' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relations
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    // Methods
    public function activate(): bool
    {
        // Désactiver toutes les autres versions de ce template
        self::where('template_id', $this->template_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        return $this->update(['is_active' => true]);
    }

    public function getFormattedSizeAttribute(): string
    {
        $sizeInBytes = strlen($this->layout ?? '') +
                      strlen($this->custom_css ?? '') +
                      strlen($this->custom_js ?? '');

        if ($sizeInBytes < 1024) {
            return $sizeInBytes . ' B';
        } elseif ($sizeInBytes < 1048576) {
            return round($sizeInBytes / 1024, 2) . ' KB';
        } else {
            return round($sizeInBytes / 1048576, 2) . ' MB';
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($version) {
            // S'assurer qu'il n'y a qu'une version active par template
            if ($version->is_active) {
                self::where('template_id', $version->template_id)
                    ->update(['is_active' => false]);
            }
        });

        static::updating(function ($version) {
            // S'assurer qu'il n'y a qu'une version active par template
            if ($version->is_active && $version->wasChanged('is_active')) {
                self::where('template_id', $version->template_id)
                    ->where('id', '!=', $version->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
