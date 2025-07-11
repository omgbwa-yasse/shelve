<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            // Configuration pour les API externes
            $table->string('api_endpoint')->nullable()->after('provider');
            $table->text('api_key')->nullable()->after('api_endpoint'); // Crypté
            $table->json('api_headers')->nullable()->after('api_key');
            $table->json('api_parameters')->nullable()->after('api_headers');

            // Métadonnées des modèles externes
            $table->string('external_model_id')->nullable()->after('api_parameters');
            $table->decimal('cost_per_token_input', 10, 8)->nullable()->after('external_model_id');
            $table->decimal('cost_per_token_output', 10, 8)->nullable()->after('cost_per_token_input');

            // Configuration par défaut
            $table->boolean('is_default')->default(false)->after('is_active');
            $table->enum('model_type', ['local', 'api'])->default('local')->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn([
                'api_endpoint',
                'api_key',
                'api_headers',
                'api_parameters',
                'external_model_id',
                'cost_per_token_input',
                'cost_per_token_output',
                'is_default',
                'model_type'
            ]);
        });
    }
};
