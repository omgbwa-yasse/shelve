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
        Schema::create('metadata_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique()->comment('Unique identifier code');
            $table->text('description')->nullable();
            $table->enum('data_type', [
                'text',
                'textarea',
                'number',
                'date',
                'datetime',
                'boolean',
                'select',
                'multi_select',
                'reference_list',
                'email',
                'url'
            ])->default('text');
            $table->json('validation_rules')->nullable()->comment('JSON validation rules');
            $table->json('options')->nullable()->comment('Options for select/multi-select fields');
            $table->foreignId('reference_list_id')->nullable()->constrained('reference_lists')->nullOnDelete();
            $table->boolean('searchable')->default(true);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('active');
            $table->index('searchable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata_definitions');
    }
};
