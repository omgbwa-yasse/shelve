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
        if (!Schema::hasTable('setting_categories')) {
            Schema::create('setting_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable(true);
                $table->foreignId('parent_id')->nullable()->constrained('setting_categories')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable(true)->constrained('setting_categories')->onDelete('set null');
                $table->string('name', 100)->unique(true)->index();
                $table->enum('type', ['integer', 'string', 'boolean', 'json', 'float', 'array']);
                $table->json('default_value');
                $table->text('description');
                $table->boolean('is_system')->default(false)->index();
                $table->json('constraints')->nullable()->comment('JSON containing min, max, options, etc.');

                // Champs fusionnés de setting_values
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('organisation_id')->nullable()->constrained('organisations')->onDelete('cascade');
                $table->json('value')->nullable()->comment('Current value (null = use default_value)');

                $table->timestamps();

                // Index pour les recherches courantes
                $table->index(['user_id', 'organisation_id']);
                $table->index(['name', 'user_id']);
                $table->index(['name', 'organisation_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('setting_categories');
    }
};
