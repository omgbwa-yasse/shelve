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
        // Table des expositions
        Schema::create('record_artifact_exhibitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artifact_id')
                ->constrained('record_artifacts')
                ->onDelete('cascade')
                ->comment('Objet exposé');

            $table->string('exhibition_name')->comment('Nom de l\'exposition');
            $table->string('venue')->nullable()->comment('Lieu de l\'exposition');
            $table->text('description')->nullable()->comment('Description de l\'exposition');
            $table->date('start_date')->comment('Date de début');
            $table->date('end_date')->nullable()->comment('Date de fin');
            $table->boolean('is_current')->default(false)->comment('Exposition en cours');
            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->timestamps();

            $table->index('artifact_id');
            $table->index('start_date');
            $table->index('is_current');
        });

        // Table des prêts
        Schema::create('record_artifact_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artifact_id')
                ->constrained('record_artifacts')
                ->onDelete('cascade')
                ->comment('Objet prêté');

            $table->string('borrower_name')->comment('Nom de l\'emprunteur');
            $table->string('borrower_contact')->nullable()->comment('Contact de l\'emprunteur');
            $table->string('borrower_address')->nullable()->comment('Adresse de l\'emprunteur');
            $table->date('loan_date')->comment('Date du prêt');
            $table->date('return_date')->nullable()->comment('Date de retour prévue');
            $table->date('actual_return_date')->nullable()->comment('Date de retour effective');
            $table->enum('status', ['active', 'returned', 'overdue', 'extended'])
                ->default('active')
                ->comment('Statut du prêt');
            $table->text('conditions')->nullable()->comment('Conditions du prêt');
            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('Approuvé par');
            $table->timestamp('approved_at')->nullable()->comment('Date d\'approbation');

            $table->timestamps();

            $table->index('artifact_id');
            $table->index('status');
            $table->index('loan_date');
            $table->index('return_date');
        });

        // Table des rapports de conservation
        Schema::create('record_artifact_condition_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artifact_id')
                ->constrained('record_artifacts')
                ->onDelete('cascade')
                ->comment('Objet inspecté');

            $table->date('report_date')->comment('Date du rapport');
            $table->enum('overall_condition', ['excellent', 'good', 'fair', 'poor', 'critical'])
                ->comment('État général');
            $table->text('observations')->comment('Observations détaillées');
            $table->text('recommendations')->nullable()->comment('Recommandations');
            $table->text('treatment_performed')->nullable()->comment('Traitement effectué');

            $table->foreignId('inspector_id')->nullable()->constrained('users')->comment('Inspecteur');
            $table->foreignId('conservator_id')->nullable()->constrained('users')->comment('Conservateur');

            $table->timestamps();

            $table->index('artifact_id');
            $table->index('report_date');
            $table->index('overall_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_artifact_condition_reports');
        Schema::dropIfExists('record_artifact_loans');
        Schema::dropIfExists('record_artifact_exhibitions');
    }
};
