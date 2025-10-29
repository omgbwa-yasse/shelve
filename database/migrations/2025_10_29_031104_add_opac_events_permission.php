<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add OPAC events permission
        $permission = [
            'name' => 'admin.opac.events',
            'category' => 'opac',
            'description' => 'Administrer les événements OPAC',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('permissions')->updateOrInsert(
            ['name' => $permission['name']],
            $permission
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'admin.opac.events')
            ->delete();
    }
};
