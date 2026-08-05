<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — Couche Support (alignement SupportDocument IntelliGID).
     *
     * `record_mediums` porte ce qui est spécifique au support (placement physique OU fichier
     * numérique) sans dupliquer la notice. Une notice peut avoir 0, 1 ou plusieurs supports
     * (ex. document numérisé = papier + numérique). Le versionnement est porté par
     * records/record_relations (Phase 2) ; record_mediums ne porte que l'état du fichier
     * pour la version courante (checkout, signature, statut draft/final/obsolete).
     */
    public function up(): void
    {
        Schema::create('record_mediums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
            $table->foreignId('support_id')->constrained('record_supports'); // papier/numérique/microfilm

            // -- Placement physique (remplace record_physical_container) --
            $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();

            // -- Fichier numérique (remplace les colonnes idoines de record_digital_documents) --
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft | final | obsolete — aligné StatutVersion (IntelliGID), distinct du statut de la notice
            // Exemplaires (Exemplaire/TypeSupportExemplaire, db.sql) — copie principale vs secondaire.
            // Un document numérisé = 2 record_mediums ; is_principal désigne l'exemplaire de référence.
            $table->boolean('is_principal')->default(true);      // Exemplaire.principal
            $table->string('copy_code')->nullable();             // Exemplaire.numero
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('signature_status', 20)->default('unsigned');
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->text('signature_data')->nullable();

            $table->unsignedBigInteger('legacy_id')->nullable();  // record_digital_documents.id d'origine
            $table->timestamps();
            $table->index(['record_id', 'support_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_mediums');
    }
};
