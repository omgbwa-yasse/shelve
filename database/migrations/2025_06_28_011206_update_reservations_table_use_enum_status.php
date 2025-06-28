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
        // Première étape : ajouter la nouvelle colonne enum status
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'in_progress', 'completed'])
                  ->default('pending')
                  ->after('user_organisation_id');
        });

        // Deuxième étape : migrer les données de status_id vers status
        DB::statement("
            UPDATE reservations
            SET status = CASE
                WHEN status_id = 1 THEN 'pending'
                WHEN status_id = 2 THEN 'approved'
                WHEN status_id = 3 THEN 'rejected'
                WHEN status_id = 4 THEN 'cancelled'
                ELSE 'pending'
            END
        ");

        // Troisième étape : supprimer la contrainte de clé étrangère et la colonne status_id
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });

        // Quatrième étape : supprimer la table reservation_statuses
        Schema::dropIfExists('reservation_statuses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table reservation_statuses
        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        // Réinsérer les données de base dans reservation_statuses
        DB::table('reservation_statuses')->insert([
            ['id' => 1, 'name' => 'Demande en cours', 'description' => 'La réservation est en cours de traitement', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Validée', 'description' => 'La réservation a été validée', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Rejetée', 'description' => 'La réservation a été rejetée', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Annulée', 'description' => 'La réservation a été annulée', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Ajouter la colonne status_id
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable(false)->after('user_organisation_id');
            $table->foreign('status_id')->references('id')->on('reservation_statuses')->onDelete('cascade');
        });

        // Migrer les données de status vers status_id
        DB::statement("
            UPDATE reservations
            SET status_id = CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'approved' THEN 2
                WHEN status = 'rejected' THEN 3
                WHEN status = 'cancelled' THEN 4
                ELSE 1
            END
        ");

        // Supprimer la colonne enum status
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
