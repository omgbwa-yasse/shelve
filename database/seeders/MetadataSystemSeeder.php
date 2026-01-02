<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetadataDefinition;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentMetadataProfile;
use App\Models\RecordDigitalFolderMetadataProfile;
use App\Models\User;

class MetadataSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first(); // Get first user as creator

        if (!$admin) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        // Create Reference Lists
        $priorityList = ReferenceList::create([
            'name' => 'Priorité',
            'code' => 'priority',
            'description' => 'Niveaux de priorité',
            'active' => true,
            'created_by' => $admin->id,
        ]);

        $priorities = [
            ['value' => 'Très haute', 'code' => 'very_high', 'sort_order' => 1],
            ['value' => 'Haute', 'code' => 'high', 'sort_order' => 2],
            ['value' => 'Moyenne', 'code' => 'medium', 'sort_order' => 3],
            ['value' => 'Basse', 'code' => 'low', 'sort_order' => 4],
        ];

        foreach ($priorities as $priority) {
            ReferenceValue::create(array_merge($priority, [
                'list_id' => $priorityList->id,
                'active' => true,
                'created_by' => $admin->id,
            ]));
        }

        $confidentialityList = ReferenceList::create([
            'name' => 'Confidentialité',
            'code' => 'confidentiality',
            'description' => 'Niveaux de confidentialité',
            'active' => true,
            'created_by' => $admin->id,
        ]);

        $confidentialities = [
            ['value' => 'Public', 'code' => 'public', 'sort_order' => 1],
            ['value' => 'Interne', 'code' => 'internal', 'sort_order' => 2],
            ['value' => 'Confidentiel', 'code' => 'confidential', 'sort_order' => 3],
            ['value' => 'Secret', 'code' => 'secret', 'sort_order' => 4],
        ];

        foreach ($confidentialities as $conf) {
            ReferenceValue::create(array_merge($conf, [
                'list_id' => $confidentialityList->id,
                'active' => true,
                'created_by' => $admin->id,
            ]));
        }

        // Create Metadata Definitions
        $definitions = [
            [
                'name' => 'Auteur',
                'code' => 'author',
                'description' => 'Auteur du document',
                'data_type' => 'text',
                'searchable' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Date de création',
                'code' => 'creation_date',
                'description' => 'Date de création du document',
                'data_type' => 'date',
                'searchable' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Priorité',
                'code' => 'priority',
                'description' => 'Niveau de priorité',
                'data_type' => 'reference_list',
                'reference_list_id' => $priorityList->id,
                'searchable' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Confidentialité',
                'code' => 'confidentiality',
                'description' => 'Niveau de confidentialité',
                'data_type' => 'reference_list',
                'reference_list_id' => $confidentialityList->id,
                'searchable' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Mots-clés',
                'code' => 'keywords',
                'description' => 'Mots-clés pour la recherche',
                'data_type' => 'textarea',
                'searchable' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Numéro de référence',
                'code' => 'reference_number',
                'description' => 'Numéro de référence externe',
                'data_type' => 'text',
                'searchable' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Date d\'échéance',
                'code' => 'due_date',
                'description' => 'Date d\'échéance ou de validité',
                'data_type' => 'date',
                'searchable' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Validé',
                'code' => 'validated',
                'description' => 'Document validé',
                'data_type' => 'boolean',
                'searchable' => false,
                'sort_order' => 8,
            ],
        ];

        $createdDefinitions = [];
        foreach ($definitions as $def) {
            $createdDefinitions[$def['code']] = MetadataDefinition::create(array_merge($def, [
                'active' => true,
                'created_by' => $admin->id,
            ]));
        }

        // Associate metadata with document types (if types exist)
        $documentTypes = RecordDigitalDocumentType::limit(3)->get();

        foreach ($documentTypes as $index => $docType) {
            // Different metadata for different document types
            $profiles = [];

            if ($index === 0) {
                // First type: contracts - needs author, dates, reference
                $profiles = [
                    ['metadata_definition_id' => $createdDefinitions['author']->id, 'mandatory' => true, 'sort_order' => 1],
                    ['metadata_definition_id' => $createdDefinitions['creation_date']->id, 'mandatory' => true, 'sort_order' => 2],
                    ['metadata_definition_id' => $createdDefinitions['reference_number']->id, 'mandatory' => true, 'sort_order' => 3],
                    ['metadata_definition_id' => $createdDefinitions['due_date']->id, 'mandatory' => false, 'sort_order' => 4],
                    ['metadata_definition_id' => $createdDefinitions['confidentiality']->id, 'mandatory' => true, 'sort_order' => 5],
                ];
            } elseif ($index === 1) {
                // Second type: invoices - needs reference, date, priority
                $profiles = [
                    ['metadata_definition_id' => $createdDefinitions['reference_number']->id, 'mandatory' => true, 'sort_order' => 1],
                    ['metadata_definition_id' => $createdDefinitions['creation_date']->id, 'mandatory' => true, 'sort_order' => 2],
                    ['metadata_definition_id' => $createdDefinitions['due_date']->id, 'mandatory' => true, 'sort_order' => 3],
                    ['metadata_definition_id' => $createdDefinitions['priority']->id, 'mandatory' => false, 'sort_order' => 4],
                    ['metadata_definition_id' => $createdDefinitions['validated']->id, 'mandatory' => false, 'sort_order' => 5],
                ];
            } else {
                // Third type: reports - needs author, keywords, validation
                $profiles = [
                    ['metadata_definition_id' => $createdDefinitions['author']->id, 'mandatory' => true, 'sort_order' => 1],
                    ['metadata_definition_id' => $createdDefinitions['creation_date']->id, 'mandatory' => true, 'sort_order' => 2],
                    ['metadata_definition_id' => $createdDefinitions['keywords']->id, 'mandatory' => false, 'sort_order' => 3],
                    ['metadata_definition_id' => $createdDefinitions['confidentiality']->id, 'mandatory' => false, 'sort_order' => 4],
                    ['metadata_definition_id' => $createdDefinitions['validated']->id, 'mandatory' => true, 'sort_order' => 5],
                ];
            }

            foreach ($profiles as $profile) {
                RecordDigitalDocumentMetadataProfile::create(array_merge($profile, [
                    'document_type_id' => $docType->id,
                    'visible' => true,
                    'readonly' => false,
                    'created_by' => $admin->id,
                ]));
            }
        }

        // Associate metadata with folder types (if types exist)
        $folderTypes = RecordDigitalFolderType::limit(2)->get();

        foreach ($folderTypes as $index => $folderType) {
            $profiles = [
                ['metadata_definition_id' => $createdDefinitions['confidentiality']->id, 'mandatory' => true, 'sort_order' => 1],
                ['metadata_definition_id' => $createdDefinitions['keywords']->id, 'mandatory' => false, 'sort_order' => 2],
            ];

            if ($index === 0) {
                $profiles[] = ['metadata_definition_id' => $createdDefinitions['priority']->id, 'mandatory' => false, 'sort_order' => 3];
            }

            foreach ($profiles as $profile) {
                RecordDigitalFolderMetadataProfile::create(array_merge($profile, [
                    'folder_type_id' => $folderType->id,
                    'visible' => true,
                    'readonly' => false,
                    'created_by' => $admin->id,
                ]));
            }
        }

        $this->command->info('Metadata system seeded successfully!');
        $this->command->info('- Created ' . count($definitions) . ' metadata definitions');
        $this->command->info('- Created 2 reference lists with values');
        $this->command->info('- Associated metadata with ' . $documentTypes->count() . ' document types');
        $this->command->info('- Associated metadata with ' . $folderTypes->count() . ' folder types');
    }
}
