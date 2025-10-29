<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpacConfiguration extends Model
{
    use HasFactory;

    protected $table = 'opac_configurations';

    protected $fillable = [
        'category_id',
        'key',
        'label',
        'description',
        'type',
        'options',
        'default_value',
        'validation_rules',
        'sort_order',
        'is_required',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(OpacConfigurationCategory::class, 'category_id');
    }

    /**
     * Relation avec les valeurs de configuration
     */
    public function values()
    {
        return $this->hasMany(OpacConfigurationValue::class, 'configuration_id');
    }

    /**
     * Récupère la valeur pour une organisation donnée
     */
    public function getValueForOrganisation($organisationId)
    {
        $value = $this->values()
                      ->where('organisation_id', $organisationId)
                      ->where('is_active', true)
                      ->first();

        if ($value) {
            return $this->castValue($value->value ?? $value->json_value);
        }

        return $this->castValue($this->default_value);
    }

    /**
     * Définit la valeur pour une organisation
     */
    public function setValueForOrganisation($organisationId, $value, $modifiedBy = null)
    {
        $configValue = $this->values()
                           ->where('organisation_id', $organisationId)
                           ->first();

        if (!$configValue) {
            $configValue = new OpacConfigurationValue();
            $configValue->organisation_id = $organisationId;
            $configValue->configuration_id = $this->id;
        }

        // Stocker selon le type
        if (in_array($this->type, ['json', 'multiselect'])) {
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

    /**
     * Cast la valeur selon le type de configuration
     */
    protected function castValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
            case 'multiselect':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    /**
     * Scope pour les configurations actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Récupère toutes les configurations avec leurs valeurs pour une organisation
     */
    public static function getConfigurationsForOrganisation($organisationId)
    {
        return self::with(['category', 'values' => function($query) use ($organisationId) {
                        $query->where('organisation_id', $organisationId)
                              ->where('is_active', true);
                    }])
                   ->active()
                   ->ordered()
                   ->get()
                   ->groupBy('category.name');
    }
}
