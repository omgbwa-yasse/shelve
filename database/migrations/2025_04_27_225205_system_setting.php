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
            $table->string('name', 100);
            $table->text('description')->nullable(true);
            $table->foreignId('parent_id')->nullable()->constrained('setting_categories')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable(false)->constrained('setting_categories')->onDelete('set null');
            $table->string('name', 100)->unique(true)->index();
            $table->enum('type', ['integer', 'string', 'boolean', 'json', 'float', 'array']);
            $table->json('default_value');
            $table->text('description');
            $table->boolean('is_system')->default(false)->index();
            $table->json('constraints')->nullable()->comment('JSON containing min, max, options, etc.');
            $table->timestamps();
        });

        Schema::create('setting_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained('settings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->onDelete('cascade');
            $table->json('value');
            $table->timestamps();
            $table->index(['user_id', 'organisation_id']);
            $table->index('setting_id');
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
