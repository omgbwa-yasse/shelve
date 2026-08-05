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
        Schema::table('notifications', function (Blueprint $table) {
            // Modifier l'enum module pour utiliser les valeurs en minuscules
            $table->dropColumn('module');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('module', [
                'bulletin_boards',
                'mails',
                'records',
                'communications',
                'transfers',
                'deposits',
                'tools',
                'dollies',
                'workflows',
                'contacts',
                'ai',
                'public',
                'settings'
            ])->default('bulletin_boards')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('module');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('module', [
                'BULLETIN_BOARDS',
                'MAILS',
                'RECORDS',
                'COMMUNICATIONS',
                'TRANSFERS',
                'DEPOSITS',
                'TOOLS',
                'DOLLIES',
                'WORKFLOWS',
                'CONTACTS',
                'AI',
                'PUBLIC',
                'SETTINGS'
            ])->default('BULLETIN_BOARDS')->after('id');
        });
    }
};
