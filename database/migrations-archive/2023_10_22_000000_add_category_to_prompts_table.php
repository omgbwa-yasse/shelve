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
        if (!Schema::hasTable('prompts')) {
            // Si la table n'existe pas, on la crée
            Schema::create('prompts', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100)->nullable();
                $table->longText('content');
                $table->boolean('is_system')->default(false)->index();
                $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        
        Schema::table('prompts', function (Blueprint $table) {
            if (!Schema::hasColumn('prompts', 'prompt_category_id')) {
                if (!Schema::hasColumn('prompts', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
                $table->foreignId('prompt_category_id')->nullable()->after('user_id')->constrained('prompt_categories')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            if (Schema::hasColumn('prompts', 'prompt_category_id')) {
                $table->dropForeign(['prompt_category_id']);
                $table->dropColumn('prompt_category_id');
            }
        });
    }
};
