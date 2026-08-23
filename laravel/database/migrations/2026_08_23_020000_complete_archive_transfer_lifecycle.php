<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_records', function (Blueprint $table) {
            if (! Schema::hasColumn('slip_records', 'record_id')) {
                $table->foreignId('record_id')
                    ->nullable()
                    ->after('slip_id')
                    ->constrained('records')
                    ->nullOnDelete();
            }

            $table->string('code', 30)->change();
        });

        Schema::table('slips', function (Blueprint $table) {
            if (! Schema::hasColumn('slips', 'transfer_type')) {
                $table->string('transfer_type', 30)->default('internal_transfer')->after('description');
            }
            if (! Schema::hasColumn('slips', 'is_rejected')) {
                $table->boolean('is_rejected')->default(false)->after('is_integrated');
                $table->dateTime('rejected_date')->nullable()->after('is_rejected');
                $table->foreignId('rejected_by')->nullable()->after('rejected_date')->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable()->after('rejected_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slips', function (Blueprint $table) {
            if (Schema::hasColumn('slips', 'is_rejected')) {
                $table->dropConstrainedForeignId('rejected_by');
                $table->dropColumn(['transfer_type', 'is_rejected', 'rejected_date', 'rejection_reason']);
            }
        });

        Schema::table('slip_records', function (Blueprint $table) {
            if (Schema::hasColumn('slip_records', 'record_id')) {
                $table->dropConstrainedForeignId('record_id');
            }
            $table->string('code', 10)->change();
        });
    }
};
