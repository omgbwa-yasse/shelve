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
        // Add transfer tracking fields to digital documents
        Schema::table('record_digital_documents', function (Blueprint $table) {
            $table->timestamp('transferred_at')->nullable()->comment('When this document was transferred to physical');
            $table->unsignedBigInteger('transferred_to_record_id')->nullable()->comment('ID of the physical record it was transferred to');
            $table->json('transfer_metadata')->nullable()->comment('Metadata about the transfer operation');

            $table->foreign('transferred_to_record_id')
                ->references('id')
                ->on('record_physicals')
                ->onDelete('set null');
        });

        // Add transfer tracking fields to digital folders
        Schema::table('record_digital_folders', function (Blueprint $table) {
            $table->timestamp('transferred_at')->nullable()->comment('When this folder was transferred to physical');
            $table->unsignedBigInteger('transferred_to_record_id')->nullable()->comment('ID of the physical record it was transferred to');
            $table->json('transfer_metadata')->nullable()->comment('Metadata about the transfer operation');

            $table->foreign('transferred_to_record_id')
                ->references('id')
                ->on('record_physicals')
                ->onDelete('set null');
        });

        // Add linked digital content field to physical records
        Schema::table('record_physicals', function (Blueprint $table) {
            $table->json('linked_digital_metadata')->nullable()->comment('Metadata about linked digital content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_digital_documents', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['transferred_to_record_id']);
            $table->dropColumn(['transferred_at', 'transferred_to_record_id', 'transfer_metadata']);
        });

        Schema::table('record_digital_folders', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['transferred_to_record_id']);
            $table->dropColumn(['transferred_at', 'transferred_to_record_id', 'transfer_metadata']);
        });

        Schema::table('record_physicals', function (Blueprint $table) {
            $table->dropColumn(['linked_digital_metadata']);
        });
    }
};
