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
        // Si vous avez besoin d'ajouter une valeur à un enum de catégorie de permission
        // cette migration peut être utilisée

        // Exemple:
        // if (Schema::hasTable('permission_categories')) {
        //     DB::statement("ALTER TABLE permission_categories MODIFY COLUMN category ENUM('admin', 'records', 'mails', 'users', 'workflow') NOT NULL");
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Si nécessaire, revert les modifications

        // Exemple:
        // if (Schema::hasTable('permission_categories')) {
        //     DB::statement("ALTER TABLE permission_categories MODIFY COLUMN category ENUM('admin', 'records', 'mails', 'users') NOT NULL");
        // }
    }
};
