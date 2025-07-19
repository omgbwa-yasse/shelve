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
        Schema::table('thesaurus_namespaces', function (Blueprint $table) {
            $table->string('description')->nullable()->after('namespace_uri')->comment('Description du namespace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thesaurus_namespaces', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
