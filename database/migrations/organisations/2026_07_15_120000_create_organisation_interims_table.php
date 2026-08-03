<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Intérim d'un responsable : un utilisateur intérimaire remplace le titulaire
     * (responsable/directeur) d'une organisation sur une période donnée.
     */
    public function up(): void
    {
        Schema::create('organisation_interims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->foreignId('titular_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('interim_user_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['organisation_id', 'titular_user_id', 'is_active'], 'org_interims_org_titular_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_interims');
    }
};
