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
        Schema::create('setting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('setting_categories')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('type', ['integer', 'string', 'boolean', 'json', 'float', 'array']);
            $table->json('default_value');
            $table->text('description');
            $table->boolean('is_system')->default(false)->index();
            $table->json('constraints')->nullable()->comment('JSON containing min, max, options, etc.');
            $table->timestamps();

            $table->unique(['name', 'category_id']);
        });

        Schema::create('setting_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained('settings')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->json('value');
            $table->timestamps();

            $table->index(['user_id', 'organisation_id']);
            $table->unique(['setting_id', 'user_id', 'organisation_id'], 'setting_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_values');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('setting_categories');
    }
};
