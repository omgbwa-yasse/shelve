<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Template extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'content',
        'settings',
        'theme',
        'is_default',
        'created_by',
        'updated_by',
        'layout',
        'custom_css',
        'custom_js',
        'variables',
        'components',
        'version',
        'meta',
        'last_modified',
        'modified_by'
    ];

    protected $casts = [
        'settings' => 'array',
        'variables' => 'array',
        'components' => 'array',
        'meta' => 'array',
        'is_default' => 'boolean',
        'last_modified' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'type' => 'opac',
        'status' => 'draft',
        'theme' => 'default',
        'version' => '1.0.0',
        'is_default' => false
    ];

    // Relations
    public function versions(): HasMany
    {
        return $this->hasMany(TemplateVersion::class);
    }

    public function previewCache(): HasMany
    {
        return $this->hasMany(TemplatePreviewCache::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Mutators & Accessors
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['draft', 'inactive']);
    }

    public function getHasVersionsAttribute(): bool
    {
        return $this->versions()->count() > 0;
    }

    // Methods
    public function createVersion(?string $description = null, ?string $createdBy = null): TemplateVersion
    {
        $nextVersion = $this->getNextVersionNumber();

        // Désactiver l'ancienne version active
        $this->versions()->where('is_active', true)->update(['is_active' => false]);

        return $this->versions()->create([
            'version' => $nextVersion,
            'layout' => $this->layout,
            'custom_css' => $this->custom_css,
            'custom_js' => $this->custom_js,
            'variables' => $this->variables,
            'components' => $this->components,
            'meta' => $this->meta,
            'created_by' => $createdBy ?? Auth::user()?->name ?? 'system',
            'change_description' => $description,
            'is_active' => true
        ]);
    }

    public function getActiveVersion(): ?TemplateVersion
    {
        return $this->versions()->where('is_active', true)->first();
    }

    public function restoreVersion(int $versionId): bool
    {
        $version = $this->versions()->findOrFail($versionId);

        $this->update([
            'layout' => $version->layout,
            'custom_css' => $version->custom_css,
            'custom_js' => $version->custom_js,
            'variables' => $version->variables,
            'components' => $version->components,
            'meta' => $version->meta,
            'last_modified' => now(),
            'modified_by' => Auth::user()?->name ?? 'system'
        ]);

        // Marquer cette version comme active
        $this->versions()->update(['is_active' => false]);
        $version->update(['is_active' => true]);

        return true;
    }

    public function clearPreviewCache(): void
    {
        $this->previewCache()->delete();
    }

    public function getPreviewCacheKey(array $variables = [], string $deviceType = 'desktop'): string
    {
        $data = [
            'template_id' => $this->id,
            'layout' => $this->layout,
            'css' => $this->custom_css,
            'js' => $this->custom_js,
            'variables' => $variables,
            'device' => $deviceType,
            'version' => $this->version
        ];

        return md5(json_encode($data));
    }

    protected function getNextVersionNumber(): string
    {
        $lastVersion = $this->versions()
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC')
            ->first();

        if (!$lastVersion) {
            return '1.0.0';
        }

        $versionParts = explode('.', $lastVersion->version);
        $versionParts[2] = (int)$versionParts[2] + 1;

        return implode('.', $versionParts);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
            $template->created_by = $template->created_by ?? Auth::user()?->name ?? 'system';
        });

        static::updating(function ($template) {
            $template->updated_by = Auth::user()?->name ?? 'system';

            if ($template->isDirty(['layout', 'custom_css', 'custom_js', 'variables', 'components'])) {
                $template->last_modified = now();
                $template->modified_by = Auth::user()?->name ?? 'system';
            }
        });

        static::updated(function ($template) {
            // Nettoyer le cache de prévisualisation si le contenu a changé
            if ($template->wasChanged(['layout', 'custom_css', 'custom_js', 'variables'])) {
                $template->clearPreviewCache();
            }
        });
    }
}
