<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpacConfiguration extends Model
{
    use HasFactory;

    protected $table = 'opac_configurations';

    protected $fillable = [
        'organisation_id',
        'config_key',
        'config_value',
        'config_type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'config_value' => 'json',
        'is_active' => 'boolean'
    ];

    /**
     * Relation avec l'organisation
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Récupère toutes les configurations pour une organisation
     */
    public static function getConfigurationsForOrganisation($organisationId)
    {
        $configs = self::where('organisation_id', $organisationId)
            ->where('is_active', true)
            ->get()
            ->keyBy('config_key');

        // Valeurs par défaut si aucune configuration n'existe
        $defaults = [
            'visible_organisations' => [$organisationId],
            'show_statistics' => true,
            'show_recent_records' => true,
            'allow_downloads' => false,
            'records_per_page' => 15,
            'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
            'show_full_record_details' => true,
            'show_attachments' => false,
            'opac_title' => 'Catalogue en ligne',
            'theme' => 'default',
            'logo_url' => null,
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'footer_text' => null,
            'contact_email' => null,
            'help_url' => null
        ];

        $result = [];
        foreach ($defaults as $key => $defaultValue) {
            if (isset($configs[$key])) {
                $result[$key] = $configs[$key]->config_value;
            } else {
                $result[$key] = $defaultValue;
            }
        }

        return $result;
    }

    /**
     * Met à jour ou crée une configuration
     */
    public static function setConfiguration($organisationId, $key, $value, $type = 'mixed', $description = null)
    {
        return self::updateOrCreate(
            [
                'organisation_id' => $organisationId,
                'config_key' => $key
            ],
            [
                'config_value' => $value,
                'config_type' => $type,
                'description' => $description,
                'is_active' => true
            ]
        );
    }

    /**
     * Récupère une configuration spécifique
     */
    public static function getConfiguration($organisationId, $key, $default = null)
    {
        $config = self::where('organisation_id', $organisationId)
            ->where('config_key', $key)
            ->where('is_active', true)
            ->first();

        return $config ? $config->config_value : $default;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('config_key', $key);
    }
}
