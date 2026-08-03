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
        // MODIFY COLUMN est spécifique à MySQL/MariaDB ; SQLite stocke les enums en texte
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE record_digital_documents MODIFY COLUMN signature_status ENUM('unsigned', 'pending', 'signed', 'rejected', 'revoked') DEFAULT 'unsigned'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE record_digital_documents MODIFY COLUMN signature_status ENUM('unsigned', 'pending', 'signed', 'rejected') DEFAULT 'unsigned'");
        }
    }
};
