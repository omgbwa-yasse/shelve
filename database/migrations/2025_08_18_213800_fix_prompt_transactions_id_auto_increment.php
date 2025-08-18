<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Only for MySQL; ensure id is AUTO_INCREMENT PRIMARY KEY
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') { return; }

        try {
            DB::statement("ALTER TABLE `prompt_transactions` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        } catch (\Throwable $e) {
            // If it already is auto_increment or cannot be modified here, ignore
        }
        try {
            DB::statement("ALTER TABLE `prompt_transactions` ADD PRIMARY KEY (`id`)");
        } catch (\Throwable $e) {
            // Primary key may already exist; ignore
        }
    }

    public function down(): void
    {
        // No-op: we won't remove AUTO_INCREMENT in down migration
    }
};
