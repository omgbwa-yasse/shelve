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
        Schema::create('external_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable()->comment('Poste ou fonction de la personne');
            $table->foreignId('external_organization_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_primary_contact')->default(false)->comment('Contact principal pour l\'organisation');
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index pour des recherches rapides
            $table->index('first_name');
            $table->index('last_name');
            $table->index('email');
            $table->index('external_organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_contacts');
    }
};
