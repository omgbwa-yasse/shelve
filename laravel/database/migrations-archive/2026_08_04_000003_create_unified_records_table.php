<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 — Couche Notice unifiée (alignement FicheDocument/FicheDossier IntelliGID).
     *
     * `records` remplace record_physicals + record_digital_folders + record_digital_documents
     * pour toute la partie "notice". La distinction dossier/document est portée par
     * record_types.is_container / record_levels, pas par une table séparée.
     */
    public function up(): void
    {
        Schema::create('record_confidentialities', function (Blueprint $table) {
            // Axe confidentialité (StatutConfidentialite, db.sql) — seedée en Phase 1,
            // alimente records.confidentiality_id. access_level (string) reste l'approximation rapide.
            $table->id();
            $table->string('code', 30)->unique();   // public | internal | confidential | secret
            $table->string('name', 150);
            $table->timestamps();
        });

        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');                              // = title / FicheDocument.titre
            $table->text('description')->nullable();
            // Champs ISAD(G) repris tels quels de record_physicals (biographical_history, archival_history,
            // acquisition_source, content, appraisal, accrual, arrangement, access_conditions,
            // reproduction_conditions, language_material, characteristic, finding_aids,
            // location_original, location_copy, related_unit, publication_note, note,
            // archivist_note, rule_convention) — copie intégrale des colonnes, nullable.
            $table->text('biographical_history')->nullable();
            $table->text('archival_history')->nullable();
            $table->text('acquisition_source')->nullable();
            $table->text('content')->nullable();
            $table->text('appraisal')->nullable();
            $table->text('accrual')->nullable();
            $table->text('arrangement')->nullable();
            $table->text('access_conditions')->nullable();
            $table->text('reproduction_conditions')->nullable();
            $table->text('language_material')->nullable();
            $table->text('characteristic')->nullable();
            $table->text('finding_aids')->nullable();
            $table->text('location_original')->nullable();
            $table->text('location_copy')->nullable();
            $table->text('related_unit')->nullable();
            $table->text('publication_note')->nullable();
            $table->text('note')->nullable();
            $table->text('archivist_note')->nullable();
            $table->text('rule_convention')->nullable();
            // Champs "Fonds" (FondsDocumentaire, db.sql) — si level_id = "Fonds" (is_container).
            // histoireAdministrative/historiqueConservation/porteeContenu → réutilisent
            // biographical_history/archival_history/content ci-dessus (pas de doublon).
            $table->text('extent')->nullable();                  // etendueUniteArchivistique
            $table->text('category_precision')->nullable();      // precisionsCategorieDocuments
            // Cycle de vie du dossier (FicheDossier, db.sql) — alimenté par le module déclassement/transfert
            $table->date('opening_date')->nullable();            // dateOuverture
            $table->date('closing_date')->nullable();            // dateFermeture
            $table->date('processing_date')->nullable();         // dateTraitement
            $table->date('transfer_approved_date')->nullable();  // dateTransfertApprouvee
            $table->date('transfer_effective_date')->nullable(); // dateTransfertReelle
            $table->date('deposit_approved_date')->nullable();   // dateVersementApprouvee
            $table->date('deposit_effective_date')->nullable();  // dateVersementReelle
            $table->date('destruction_approved_date')->nullable(); // dateDestructionApprouvee
            $table->date('destruction_effective_date')->nullable(); // dateDestructionReelle
            $table->date('last_reminder_date')->nullable();      // dateDernierRappel
            $table->string('old_record_number')->nullable();     // ancienNumeroDossier
            $table->string('archival_status_gvaa')->nullable();  // statutArchivistiqueGVAA
            $table->boolean('unavailable')->default(false);      // indisponible
            $table->boolean('annual_opening')->default(false);   // ouvertureAnnuelle
            $table->boolean('is_essential')->default(false);     // statutEssentiel
            // Prêt/emprunt de la NOTICE (dateEmprunt/id_emprunteur/datePrevueRetourEmprunt/
            // dateRetourEmpruntReelle/modifieApresEmprunt sur FicheDocument ET FicheDossier, db.sql).
            // NB : le prêt/checkout du FICHIER reste sur record_mediums (Phase 3). Alternative métier
            // possible : traiter le prêt de notice via le module Communication existant
            // (return_date/return_effective) — colonnes ajoutées par défaut pour la parité IntelliGID.
            $table->foreignId('loaned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('loaned_at')->nullable();
            $table->dateTime('loan_planned_return_at')->nullable();
            $table->dateTime('loan_actual_return_at')->nullable();
            $table->boolean('modified_after_loan')->default(false);
            // Axe confidentialité / limites d'accès (StatutConfidentialite + LimiteAcces, db.sql)
            $table->foreignId('confidentiality_id')->nullable()->constrained('record_confidentialities')->nullOnDelete();
            $table->foreignId('access_limit_id')->nullable()->constrained('reference_lists')->nullOnDelete();
            // Champs descriptifs non-ISAD (FicheDocument/FicheDossier, db.sql)
            $table->text('table_of_contents')->nullable();       // tableMatieres
            $table->string('quantity')->nullable();              // quantite
            $table->string('dimension')->nullable();             // dimension
            $table->string('publisher')->nullable();             // editeur
            $table->date('publication_date')->nullable();        // datePublication
            $table->dateTime('sent_date')->nullable();           // dateExpedition
            $table->dateTime('received_date')->nullable();       // dateReception
            $table->dateTime('signature_date')->nullable();      // dateSignature (sur la notice)
            $table->boolean('final_version_creation')->default(false); // versionFinaleCreation
            $table->string('location_before_add')->nullable();   // emplacementAvantAjout
            $table->string('sort_value')->nullable();            // valeurTri
            // Portée géographique (PorteeGeographique, db.sql) — liste de lieux libres.
            // Limitation actée : les dates multiples (PorteeTemporelle en lignes) restent une plage
            // unique (start_date/end_date/date_exact) — refus explicite de la duplication en lignes.
            $table->json('geographic_scope')->nullable();

            $table->foreignId('type_id')->nullable()->constrained('record_types')->nullOnDelete();
            $table->foreignId('level_id')->constrained('record_levels');
            $table->foreignId('status_id')->constrained('record_statuses');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('records')->nullOnDelete();
            $table->foreignId('organisation_id')->constrained('organisations'); // corrige le bug existant (colonne absente sur record_physicals)
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('access_level', 20)->default('internal');
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();                 // valeurs des champs du profil (Phase 1)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('date_exact')->nullable();
            $table->string('date_format', 1)->nullable();
            // Versionnement — chaque version est une notice à part entière (aligné IntelliGID
            // FicheDocument/RelationEstVersionDeFicheDoc), voir "record_relations".
            $table->integer('version_number')->default(1);
            $table->boolean('is_current_version')->default(true);
            // Traçabilité de migration (retirées en Phase 7)
            $table->string('legacy_source', 20)->nullable();      // 'physical' | 'digital_folder' | 'digital_document'
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['legacy_source', 'legacy_id']);
            $table->index(['organisation_id', 'status_id']);
            $table->index(['type_id', 'parent_id']);
            $table->index('is_current_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
        Schema::dropIfExists('record_confidentialities');
    }
};
