<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;
use App\Models\MetadataDefinition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LinkMetadataToTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        if (!$admin) {
            $this->command->error('Aucun utilisateur trouvé !');
            return;
        }

        $this->command->info("\n🔗 Liaison des métadonnées aux types...\n");

        // Récupérer quelques métadonnées génériques (les 5 premières)
        $metadataDefinitions = MetadataDefinition::orderBy('id')->limit(5)->get();

        if ($metadataDefinitions->isEmpty()) {
            $this->command->error('Aucune métadonnée trouvée. Exécutez d\'abord le seeder de métadonnées.');
            return;
        }

        $this->command->info("Métadonnées à associer:");
        foreach ($metadataDefinitions as $meta) {
            $this->command->info("  - {$meta->code} ({$meta->name}) - {$meta->data_type}");
        }

        // Lier aux types de folders
        $this->command->info("\n📁 Association aux types de folders...");
        $folderTypes = RecordDigitalFolderType::where('id', '>', 1)->get(); // Sauf le type GENERAL

        $folderCount = 0;
        foreach ($folderTypes as $type) {
            $this->command->info("  → {$type->name}");

            foreach ($metadataDefinitions as $index => $metadata) {
                // Vérifier si l'association existe déjà
                $exists = DB::table('record_digital_folder_metadata_profiles')
                    ->where('folder_type_id', $type->id)
                    ->where('metadata_definition_id', $metadata->id)
                    ->exists();

                if (!$exists) {
                    DB::table('record_digital_folder_metadata_profiles')->insert([
                        'folder_type_id' => $type->id,
                        'metadata_definition_id' => $metadata->id,
                        'mandatory' => $index === 0, // Premier champ obligatoire
                        'visible' => true,
                        'readonly' => false,
                        'sort_order' => $index,
                        'created_by' => $admin->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $folderCount++;
                }
            }
        }

        // Lier aux types de documents
        $this->command->info("\n📝 Association aux types de documents...");
        $documentTypes = RecordDigitalDocumentType::where('id', '>', 1)->get(); // Sauf le type GENERAL

        $documentCount = 0;
        foreach ($documentTypes as $type) {
            $this->command->info("  → {$type->name}");

            foreach ($metadataDefinitions as $index => $metadata) {
                // Vérifier si l'association existe déjà
                $exists = DB::table('record_digital_document_metadata_profiles')
                    ->where('document_type_id', $type->id)
                    ->where('metadata_definition_id', $metadata->id)
                    ->exists();

                if (!$exists) {
                    DB::table('record_digital_document_metadata_profiles')->insert([
                        'document_type_id' => $type->id,
                        'metadata_definition_id' => $metadata->id,
                        'mandatory' => $index === 0, // Premier champ obligatoire
                        'visible' => true,
                        'readonly' => false,
                        'sort_order' => $index,
                        'created_by' => $admin->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $documentCount++;
                }
            }
        }

        $this->command->info("\n✅ Terminé!");
        $this->command->info("  📁 {$folderCount} associations créées pour les folders");
        $this->command->info("  📝 {$documentCount} associations créées pour les documents");
    }
}
