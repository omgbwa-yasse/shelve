<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('ai_global_settings')->insert([
            [
                'setting_key' => 'default_model_id',
                'setting_value' => null,
                'setting_type' => 'integer',
                'description' => 'ID du modèle AI par défaut à utiliser',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'default_provider',
                'setting_value' => 'ollama',
                'setting_type' => 'string',
                'description' => 'Fournisseur AI par défaut (ollama, openai, anthropic, grok)',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'fallback_model_id',
                'setting_value' => null,
                'setting_type' => 'integer',
                'description' => 'ID du modèle de fallback en cas d\'erreur',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'max_retries',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'Nombre maximum de tentatives en cas d\'échec',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'auto_sync_ollama',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Synchroniser automatiquement les modèles Ollama',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_global_settings');
    }
};
