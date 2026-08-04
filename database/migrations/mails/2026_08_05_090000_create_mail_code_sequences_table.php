<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Séquences de numérotation du registre de courrier.
 *
 * Jusqu'ici, chaque contrôleur recalculait son numéro par `count() + 1` suivi d'une
 * boucle `exists()` — cinq implémentations dupliquées, aucune transactionnelle : deux
 * enregistrements simultanés obtenaient le même numéro. Cette table matérialise le
 * compteur pour qu'il puisse être verrouillé (`lockForUpdate`) le temps de
 * l'attribution.
 *
 * `register` distingue les suites : 'mail' (numéro par typologie, format
 * AAAA/CODE/0001), 'reply_in' / 'reply_out' / 'reply_int' (format XX-AAAA-NNN), et
 * plus tard 'bordereau'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_code_sequences')) {
            return;
        }

        Schema::create('mail_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('register', 30);
            $table->unsignedSmallInteger('year');
            $table->foreignId('typology_id')->nullable()
                ->constrained('mail_typologies')->nullOnDelete();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['register', 'year', 'typology_id'], 'mail_code_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_code_sequences');
    }
};
