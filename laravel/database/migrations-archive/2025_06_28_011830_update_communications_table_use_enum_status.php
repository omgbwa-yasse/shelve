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
        Schema::table('communications', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_consultation', 'returned'])
                  ->default('pending')
                  ->after('return_effective');
        });

        // Deuxième étape : migrer les données de status_id vers status
        DB::statement("
            UPDATE communications
            SET status = CASE
                WHEN status_id = 1 THEN 'pending'
                WHEN status_id = 2 THEN 'approved'
                WHEN status_id = 3 THEN 'rejected'
                WHEN status_id = 4 THEN 'in_consultation'
                WHEN status_id = 5 THEN 'returned'
                ELSE 'pending'
            END
        ");

        // Troisième étape : supprimer la contrainte de clé étrangère et la colonne status_id
        Schema::table('communications', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });

        // Quatrième étape : supprimer la table communication_statuses
        Schema::dropIfExists('communication_statuses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table communication_statuses
        Schema::create('communication_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        // Réinsérer les données de base dans communication_statuses
        DB::table('communication_statuses')->insert([
            ['id' => 1, 'name' => 'Demande en cours', 'description' => 'La demande est en cours de traitement', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Validée', 'description' => 'La demande a été validée', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Rejetée', 'description' => 'La demande a été rejetée', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'En consultation', 'description' => 'Les documents sont en consultation', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Retournée', 'description' => 'Les documents ont été retournés', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Ajouter la colonne status_id
        Schema::table('communications', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable(false)->after('return_effective');
            $table->foreign('status_id')->references('id')->on('communication_statuses')->onDelete('cascade');
        });

        // Migrer les données de status vers status_id
        DB::statement("
            UPDATE communications
            SET status_id = CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'approved' THEN 2
                WHEN status = 'rejected' THEN 3
                WHEN status = 'in_consultation' THEN 4
                WHEN status = 'returned' THEN 5
                ELSE 1
            END
        ");

        // Supprimer la colonne enum status
        Schema::table('communications', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
