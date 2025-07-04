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
        Schema::create('external_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_form')->nullable()->comment('Forme juridique: SARL, SA, etc.');
            $table->string('registration_number')->nullable()->comment('Numéro d\'immatriculation/SIRET/etc.');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable()->default('France');
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index pour des recherches rapides
            $table->index('name');
            $table->index('registration_number');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_organizations');
    }
};
