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
        if (!Schema::hasColumn('containers', 'x_position')) {
            Schema::table('containers', function (Blueprint $table) {
                $table->integer('x_position')->nullable()->after('property_id');
                $table->integer('y_position')->nullable()->after('x_position');
                $table->integer('z_position')->nullable()->after('y_position');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['x_position', 'y_position', 'z_position']);
        });
    }
};
