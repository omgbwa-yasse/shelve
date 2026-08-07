<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre des risques projet — même patron que `project_milestones` /
 * `project_deliverables` (voir 2026_08_05_100700_add_project_management_features.php) :
 * pas d'`organisation_id` propre, l'autorisation délègue à `ProjectPolicy` via
 * le `Project` parent. `probability`/`impact` sont des échelles qualitatives
 * (low|medium|high) — suffisant pour un registre de risques standard, la
 * criticité (score) se calcule à la volée (`ProjectRisk::getScoreAttribute()`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('category', 30)->default('other'); // technical|financial|schedule|resource|external|other
            $table->string('probability', 10)->default('medium'); // low|medium|high
            $table->string('impact', 10)->default('medium'); // low|medium|high
            $table->string('status', 20)->default('open'); // open|mitigated|closed|occurred
            $table->text('mitigation_plan')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('review_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};
