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
        Schema::table('permissions', function (Blueprint $table) {
            $table->enum('category', [
                'dashboard',
                'mail',
                'records',
                'communications',
                'reservations',
                'transfers',
                'deposits',
                'users',
                'settings',
                'system',
                'reports',
                'tools',
                'ai',
                'backups',
                'search',
                'thesaurus',
                'organizations'
            ])->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
