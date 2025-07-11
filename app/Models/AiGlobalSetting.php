<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiGlobalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_encrypted'
    ];

    protected $casts = [
        'is_encrypted' => 'boolean'
    ];

    // Accessors et Mutators pour le cryptage
    public function getSettingValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Conversion automatique selon le type
        switch ($this->setting_type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function setSettingValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $this->attributes['setting_value'] = Crypt::encryptString($value);
        } else {
            // Conversion selon le type
            switch ($this->setting_type) {
                case 'json':
                    $this->attributes['setting_value'] = json_encode($value);
                    break;
                case 'boolean':
                    $this->attributes['setting_value'] = $value ? 'true' : 'false';
                    break;
                default:
                    $this->attributes['setting_value'] = $value;
            }
        }
    }

    // Méthodes statiques pour faciliter l'utilisation
    public static function get(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $description = null, bool $isEncrypted = false)
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'description' => $description,
                'is_encrypted' => $isEncrypted
            ]
        );
    }

    public static function getDefaultModelId(): ?int
    {
        return static::get('default_model_id');
    }

    public static function setDefaultModelId(int $modelId): void
    {
        static::set('default_model_id', $modelId, 'integer', 'ID du modèle AI par défaut');
    }

    public static function getDefaultProvider(): string
    {
        return static::get('default_provider', 'ollama');
    }

    public static function setDefaultProvider(string $provider): void
    {
        static::set('default_provider', $provider, 'string', 'Fournisseur AI par défaut');
    }
}
