<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            if (! Schema::hasColumn('buildings', 'address')) {
                $table->string('address', 255)->nullable()->after('description');
            }
            if (! Schema::hasColumn('buildings', 'capacity')) {
                $table->unsignedInteger('capacity')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            if (Schema::hasColumn('buildings', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('buildings', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
