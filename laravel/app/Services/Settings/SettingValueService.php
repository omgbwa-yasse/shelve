<?php

namespace App\Services\Settings;

use App\Models\Setting;

/**
 * Conversion et validation des valeurs de paramètres applicatifs — D01.
 *
 * Extraite de `SettingController` (Blade) au portage de D01 (ruban e du plan) :
 * le contrôleur Blade et l'API v1 partagent cette unique source de vérité, sans
 * quoi l'API accepterait des valeurs que Blade refusait (risque R01).
 */
class SettingValueService
{
    /**
     * Convertit une valeur brute (string de formulaire ou JSON) vers le type du paramètre.
     */
    public function convertValueToType(mixed $value, string $type): mixed
    {
        switch ($type) {
            case 'integer':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'boolean':
                if (is_string($value)) {
                    $lowerValue = strtolower(trim($value));
                    if (in_array($lowerValue, ['true', '1', 'yes', 'on'])) {
                        return true;
                    }
                    if (in_array($lowerValue, ['false', '0', 'no', 'off', ''])) {
                        return false;
                    }
                }

                return (bool) $value;

            case 'array':
            case 'json':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if ($decoded !== null) {
                        return $decoded;
                    }
                }

                return $value;

            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Valide la valeur convertie contre le type attendu et les contraintes du paramètre.
     */
    public function validateValueType(mixed $value, string $type, mixed $constraints = null): bool
    {
        if (is_array($constraints)) {
            // déjà un array, on le garde
        } elseif (is_string($constraints)) {
            $constraints = json_decode($constraints, true) ?? [];
        } else {
            $constraints = [];
        }

        return match ($type) {
            'integer' => $this->validateIntegerType($value, $constraints),
            'float' => $this->validateFloatType($value, $constraints),
            'string' => $this->validateStringType($value, $constraints),
            'boolean' => is_bool($value),
            'array' => $this->validateArrayType($value, $constraints),
            'json' => true, // re-validé au décodage
            default => false,
        };
    }

    /**
     * Persiste la valeur personnalisée d'un paramètre, ou la réinitialise si vide.
     * Retourne true si la valeur a été modifiée.
     */
    public function saveValue(Setting $setting, mixed $value): bool
    {
        if ($value === null || ($value === '' && $value !== false && $value !== 0 && $value !== '0')) {
            if ($setting->value !== null) {
                $setting->value = null;
                $setting->save();

                return true;
            }

            return false;
        }

        $converted = $this->convertValueToType($value, $setting->type);

        if (!$this->validateValueType($converted, $setting->type, $setting->constraints ?? [])) {
            return false;
        }

        $setting->value = $converted;
        $setting->save();

        return true;
    }

    private function validateIntegerType(mixed $value, array $constraints): bool
    {
        if (!is_int($value)) {
            return false;
        }

        if (isset($constraints['min']) && $value < $constraints['min']) {
            return false;
        }

        if (isset($constraints['max']) && $value > $constraints['max']) {
            return false;
        }

        return true;
    }

    private function validateFloatType(mixed $value, array $constraints): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        if (isset($constraints['min']) && $value < $constraints['min']) {
            return false;
        }

        if (isset($constraints['max']) && $value > $constraints['max']) {
            return false;
        }

        return true;
    }

    private function validateStringType(mixed $value, array $constraints): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if (isset($constraints['min_length']) && strlen($value) < $constraints['min_length']) {
            return false;
        }

        if (isset($constraints['max_length']) && strlen($value) > $constraints['max_length']) {
            return false;
        }

        if (isset($constraints['pattern']) && !preg_match($constraints['pattern'], $value)) {
            return false;
        }

        return true;
    }

    private function validateArrayType(mixed $value, array $constraints): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if (isset($constraints['min_items']) && count($value) < $constraints['min_items']) {
            return false;
        }

        if (isset($constraints['max_items']) && count($value) > $constraints['max_items']) {
            return false;
        }

        return true;
    }
}
