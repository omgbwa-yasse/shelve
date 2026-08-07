<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Interrupteur par organisation pour le module Email (boîte IMAP/SMTP) — off
 * par défaut : un administrateur doit l'activer explicitement depuis
 * Paramètres avant que la section "Email" n'apparaisse dans le menu Mails.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('email_module_enabled')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('email_module_enabled');
        });
    }
};
