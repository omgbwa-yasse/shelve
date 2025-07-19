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
        Schema::table('thesaurus_schemes', function (Blueprint $table) {
            $table->foreignId('namespace_id')
                  ->nullable()
                  ->after('language')
                  ->constrained('thesaurus_namespaces')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thesaurus_schemes', function (Blueprint $table) {
            $table->dropForeign(['namespace_id']);
            $table->dropColumn('namespace_id');
        });
    }
};
