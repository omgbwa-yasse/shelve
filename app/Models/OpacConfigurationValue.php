<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpacConfigurationValue extends Model
{
    use HasFactory;

    protected $table = 'opac_configuration_values';

    protected $fillable = [
        'organisation_id',
        'configuration_id',
        'value',
        'json_value',
        'is_active',
        'last_modified_at',
        'modified_by'
    ];

    protected $casts = [
        'json_value' => 'array',
        'is_active' => 'boolean',
        'last_modified_at' => 'datetime'
    ];

    /**
     * Relation avec l'organisation
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec la configuration
     */
    public function configuration()
    {
        return $this->belongsTo(OpacConfiguration::class, 'configuration_id');
    }

    /**
     * Relation avec l'utilisateur qui a modifié
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Récupère la valeur effective (value ou json_value)
     */
    public function getEffectiveValueAttribute()
    {
        return $this->json_value !== null ? $this->json_value : $this->value;
    }

    /**
     * Scope pour les valeurs actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Récupère les valeurs de configuration pour une organisation
     */
    public static function getValuesForOrganisation($organisationId)
    {
        return self::with(['configuration.category'])
                   ->where('organisation_id', $organisationId)
                   ->active()
                   ->get()
                   ->keyBy('configuration.key');
    }

    /**
     * Met à jour ou crée une valeur de configuration
     */
    public static function updateOrCreateValue($organisationId, $configurationId, $value, $modifiedBy = null)
    {
        $configValue = self::where('organisation_id', $organisationId)
                          ->where('configuration_id', $configurationId)
                          ->first();

        if (!$configValue) {
            $configValue = new self();
            $configValue->organisation_id = $organisationId;
            $configValue->configuration_id = $configurationId;
        }

        // Détermine comment stocker la valeur
        $configuration = OpacConfiguration::find($configurationId);
        if ($configuration && in_array($configuration->type, ['json', 'multiselect'])) {
            $configValue->json_value = is_array($value) ? $value : json_decode($value, true);
            $configValue->value = null;
        } else {
            $configValue->value = $value;
            $configValue->json_value = null;
        }

        $configValue->is_active = true;
        $configValue->last_modified_at = now();
        $configValue->modified_by = $modifiedBy;
        $configValue->save();

        return $configValue;
    }
}
