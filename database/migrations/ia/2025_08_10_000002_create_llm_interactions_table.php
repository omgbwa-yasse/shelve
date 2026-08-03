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
        Schema::create('llm_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->index();
            $table->string('request_id', 100)->nullable(); // plus flexible, composite unique avec provider
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider', 64);
            $table->string('model', 128);
            $table->string('source', 32); // mcp|api|web|batch etc.
            $table->string('status', 32); // success|error|timeout|cancelled
            $table->string('error_code', 64)->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->decimal('temperature', 4, 2)->nullable();
            $table->decimal('top_p', 5, 4)->nullable();
            $table->unsignedBigInteger('cost_microusd')->default(0); // coût en micro-dollars
            $table->timestamp('started_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Index simples
            $table->index('provider');
            $table->index('model');
            $table->index('source');
            $table->index('status');
            $table->index('error_code');
            $table->index('completed_at');

            // Index composites analytiques
            $table->unique(['provider','request_id']); // évite collisions cross-provider
            $table->index(['user_id', 'started_at']);
            $table->index(['started_at','provider','model']);
            $table->index(['started_at','source']);
            $table->index(['status','started_at']);
            $table->index(['provider','model']); // redondant intentionnel pour optimiser certains planners
        });

        Schema::create('llm_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('provider', 64);
            $table->string('model', 128);
            $table->string('source', 32); // mcp|api|web|batch etc.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('requests_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedBigInteger('total_prompt_tokens')->default(0);
            $table->unsignedBigInteger('total_completion_tokens')->default(0);
            $table->unsignedBigInteger('total_tokens')->default(0);
            $table->unsignedBigInteger('total_cost_microusd')->default(0);
            $table->unsignedInteger('avg_latency_ms')->default(0);
            $table->unsignedInteger('max_latency_ms')->default(0);
            $table->timestamps();

            $table->unique(['date', 'provider', 'model', 'source', 'user_id'], 'llm_daily_stats_unique');
            $table->index(['date','provider','model','source']);
            $table->index(['provider','model']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('llm_daily_stats');
    Schema::dropIfExists('llm_interactions');
    }
};
