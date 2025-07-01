<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->foreignId('assigned_organisation_id')->nullable()->constrained('organisations')->onDelete('set null')->after('assigned_to');
            $table->index('assigned_organisation_id');
        });
    }

    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_organisation_id');
        });
    }
};
