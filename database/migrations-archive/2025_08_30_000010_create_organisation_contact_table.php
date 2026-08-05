<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organisation_contact')) {
            Schema::create('organisation_contact', function (Blueprint $table) {
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('contact_id');

                // Attributs de lien optionnels
                $table->boolean('is_primary')->default(false);

                $table->unique(['organisation_id', 'contact_id'], 'org_contact_unique');

                $table->foreign('organisation_id')
                    ->references('id')->on('organisations')
                    ->cascadeOnDelete();

                $table->foreign('contact_id')
                    ->references('id')->on('contacts')
                    ->cascadeOnDelete();

                $table->index('organisation_id');
                $table->index('contact_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_contact');
    }
};
