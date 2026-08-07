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
        // Update record_physical_attachment table to use consistent column naming
        if (Schema::hasTable('record_physical_attachment') && Schema::hasColumn('record_physical_attachment', 'record_id')) {
            Schema::table('record_physical_attachment', function (Blueprint $table) {
                $table->renameColumn('record_id', 'record_physical_id');
            });
        }

        // Update record_physical_container table to use consistent column naming
        if (Schema::hasTable('record_physical_container') && Schema::hasColumn('record_physical_container', 'record_id')) {
            Schema::table('record_physical_container', function (Blueprint $table) {
                $table->renameColumn('record_id', 'record_physical_id');
            });
        }

        // Update record_physical_thesaurus_concept table to use consistent column naming
        if (Schema::hasTable('record_physical_thesaurus_concept') && Schema::hasColumn('record_physical_thesaurus_concept', 'record_id')) {
            Schema::table('record_physical_thesaurus_concept', function (Blueprint $table) {
                $table->renameColumn('record_id', 'record_physical_id');
            });
        }

        // Update record_physical_links table to use consistent column naming
        if (Schema::hasTable('record_physical_links') && Schema::hasColumn('record_physical_links', 'record_id')) {
            Schema::table('record_physical_links', function (Blueprint $table) {
                $table->renameColumn('record_id', 'record_physical_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert column name changes
        if (Schema::hasTable('record_physical_links') && Schema::hasColumn('record_physical_links', 'record_physical_id')) {
            Schema::table('record_physical_links', function (Blueprint $table) {
                $table->renameColumn('record_physical_id', 'record_id');
            });
        }

        if (Schema::hasTable('record_physical_thesaurus_concept') && Schema::hasColumn('record_physical_thesaurus_concept', 'record_physical_id')) {
            Schema::table('record_physical_thesaurus_concept', function (Blueprint $table) {
                $table->renameColumn('record_physical_id', 'record_id');
            });
        }

        if (Schema::hasTable('record_physical_container') && Schema::hasColumn('record_physical_container', 'record_physical_id')) {
            Schema::table('record_physical_container', function (Blueprint $table) {
                $table->renameColumn('record_physical_id', 'record_id');
            });
        }

        if (Schema::hasTable('record_physical_attachment') && Schema::hasColumn('record_physical_attachment', 'record_physical_id')) {
            Schema::table('record_physical_attachment', function (Blueprint $table) {
                $table->renameColumn('record_physical_id', 'record_id');
            });
        }
    }
};
