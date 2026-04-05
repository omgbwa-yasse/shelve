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
        Schema::table('record_digital_documents', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('record_digital_documents', 'signature_hash')) {
                $blueprint->string('signature_hash')->nullable()->after('signature_data');
            }
            if (!Schema::hasColumn('record_digital_documents', 'signature_revoked_at')) {
                $blueprint->timestamp('signature_revoked_at')->nullable()->after('signature_hash');
            }
            if (!Schema::hasColumn('record_digital_documents', 'signature_revocation_reason')) {
                $blueprint->text('signature_revocation_reason')->nullable()->after('signature_revoked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_digital_documents', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['signature_hash', 'signature_revoked_at', 'signature_revocation_reason']);
        });
    }
};
