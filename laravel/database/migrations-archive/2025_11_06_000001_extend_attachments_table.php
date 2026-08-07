<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ajouter les nouveaux types ENUM
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent',
            'digital_folder',
            'digital_document',
            'artifact',
            'book',
            'periodic'
        ) NOT NULL");

        // 2. Ajouter les colonnes de métadonnées
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('ocr_language', 10)->nullable()->after('content_text');
            $table->decimal('ocr_confidence', 5, 2)->nullable()->after('ocr_language')->comment('Score qualité OCR 0-100');
            $table->string('file_encoding', 50)->nullable()->after('mime_type');
            $table->integer('page_count')->nullable()->after('ocr_confidence')->comment('Nombre de pages PDF');
            $table->integer('word_count')->nullable()->after('page_count');
            $table->string('file_hash_md5', 32)->nullable()->after('crypt_sha512');
            $table->string('file_extension', 10)->nullable()->after('mime_type');
            $table->boolean('is_primary')->default(false)->after('type')->comment('Fichier principal');
            $table->integer('display_order')->default(0)->after('is_primary');
            $table->text('description')->nullable()->after('name');

            // Index de performance
            $table->index(['type', 'is_primary'], 'idx_type_primary');
            $table->index('file_hash_md5', 'idx_file_hash');
            $table->index('file_extension', 'idx_extension');
            $table->index('display_order', 'idx_display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('idx_type_primary');
            $table->dropIndex('idx_file_hash');
            $table->dropIndex('idx_extension');
            $table->dropIndex('idx_display_order');

            $table->dropColumn([
                'ocr_language',
                'ocr_confidence',
                'file_encoding',
                'page_count',
                'word_count',
                'file_hash_md5',
                'file_extension',
                'is_primary',
                'display_order',
                'description',
            ]);
        });

        // Restaurer l'ENUM original
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent'
        ) NOT NULL");
    }
};
