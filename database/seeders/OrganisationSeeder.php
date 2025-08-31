<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Supprimer toute l'infrastructure physique existante
            $this->command->info('🗑️ Suppression de l\'infrastructure physique existante...');
            Container::query()->delete();
            Shelf::query()->delete();
            Room::query()->delete();
            Floor::query()->delete();
            Building::query()->delete();
            ContainerProperty::query()->delete();

            // Supprimer tous les contacts et liaisons existants
            $this->command->info('🗑️ Suppression des contacts existants...');
            Contact::query()->delete();

            // Supprimer toutes les organisations existantes
            $this->command->info('🗑️ Suppression des organisations existantes...');
            Organisation::query()->delete();

            // Créer la nouvelle structure organisationnelle
            $this->command->info('🏢 Création de la nouvelle structure organisationnelle...');

            // Direction Générale (DG) - Organisation racine
            $directionGenerale = Organisation::create([
                'code' => 'DG',
                'name' => 'Direction Générale',
                'description' => 'Direction générale de l\'organisation',
                'parent_id' => null
            ]);

            // Direction des Finances (DF)
            $directionFinances = Organisation::create([
                'code' => 'DF',
                'name' => 'Direction des Finances',
                'description' => 'Direction responsable de la gestion financière',
                'parent_id' => $directionGenerale->id
            ]);

            // Direction des Ressources Humaines (DRH)
            $directionRH = Organisation::create([
                'code' => 'DRH',
                'name' => 'Direction des Ressources Humaines',
                'description' => 'Direction responsable de la gestion des ressources humaines',
                'parent_id' => $directionGenerale->id
            ]);

            // Direction des Archives et Documents Administratifs (DADA)
            $directionArchives = Organisation::create([
                'code' => 'DADA',
                'name' => 'Direction des Archives et Documents Administratifs',
                'description' => 'Direction responsable de la gestion des archives et documents administratifs',
                'parent_id' => $directionGenerale->id
            ]);

            $this->command->info('✅ Structure organisationnelle créée');

            // Associer des contacts par défaut à chaque organisation
            foreach ([$directionGenerale, $directionFinances, $directionRH, $directionArchives] as $org) {
                $this->addDefaultContacts($org);
            }

            // Créer l'infrastructure physique
            $this->createPhysicalInfrastructure($directionFinances, $directionRH, $directionArchives);

            DB::commit();
            $this->command->info('✅ Infrastructure organisationnelle et physique créée avec succès');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Erreur lors de la création: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer l'infrastructure physique
     */
    private function createPhysicalInfrastructure($directionFinances, $directionRH, $directionArchives)
    {
        $this->command->info('🏗️ Création de l\'infrastructure physique...');

        // Créer une propriété de boîte par défaut
        $defaultProperty = ContainerProperty::firstOrCreate(
            ['name' => 'Boîte Archive Standard'],
            [
                'name' => 'Boîte Archive Standard',
                'width' => 35.0,  // 35 cm
                'length' => 25.0, // 25 cm
                'depth' => 12.0,  // 12 cm
                'creator_id' => 999999 // Valeur temporaire, sera mise à jour par SuperAdminSeeder
            ]
        );

        // Créer le bâtiment principal
        $building = Building::create([
            'name' => 'Bâtiment Principal Archives',
            'description' => 'Bâtiment principal pour le stockage des archives administratives',
            'visibility' => 'public',
            'creator_id' => 999999 // Valeur temporaire, sera mise à jour par SuperAdminSeeder
        ]);

        $directions = [
            ['org' => $directionFinances, 'floor_num' => 1, 'room_code' => 'SALLE-DF'],
            ['org' => $directionRH, 'floor_num' => 2, 'room_code' => 'SALLE-DRH'],
            ['org' => $directionArchives, 'floor_num' => 3, 'room_code' => 'SALLE-DADA']
        ];

        foreach ($directions as $directionData) {
            // Créer l'étage
            $floor = Floor::create([
                'name' => 'Étage ' . $directionData['floor_num'],
                'description' => 'Étage dédié à la ' . $directionData['org']->name,
                'building_id' => $building->id,
                'creator_id' => 999999
            ]);

            // Créer la salle
            $room = Room::create([
                'code' => $directionData['room_code'],
                'name' => 'Salle ' . $directionData['org']->code,
                'description' => 'Salle d\'archives pour la ' . $directionData['org']->name,
                'visibility' => 'public',
                'type' => 'archives',
                'floor_id' => $floor->id,
                'creator_id' => 999999
            ]);

            // Associer la salle à l'organisation
            $room->organisations()->attach($directionData['org']->id);

            // Créer 10 étagères dans la salle
            for ($shelfNum = 1; $shelfNum <= 10; $shelfNum++) {
                $shelf = Shelf::create([
                    'code' => $directionData['room_code'] . '-ET' . str_pad($shelfNum, 2, '0', STR_PAD_LEFT),
                    'observation' => 'Étagère ' . $shelfNum . ' de la salle ' . $directionData['org']->code,
                    'face' => 1, // Face numérique au lieu de 'A'
                    'ear' => 1,
                    'shelf' => $shelfNum,
                    'shelf_length' => 200, // 2 mètres
                    'room_id' => $room->id,
                    'creator_id' => 999999
                ]);

                // Créer 10 boîtes d'archives sur chaque étagère
                for ($boxNum = 1; $boxNum <= 10; $boxNum++) {
                    Container::create([
                        'code' => $shelf->code . '-B' . str_pad($boxNum, 2, '0', STR_PAD_LEFT),
                        'shelve_id' => $shelf->id,
                        'status_id' => 1, // Statut par défaut
                        'property_id' => $defaultProperty->id, // Utiliser la propriété par défaut
                        'creator_id' => 999999,
                        'creator_organisation_id' => $directionData['org']->id
                    ]);
                }
            }

            $this->command->info('✅ Infrastructure créée pour ' . $directionData['org']->name . ' (Étage ' . $directionData['floor_num'] . ')');
        }

        $this->command->info('✅ Infrastructure physique complète créée');
        $this->command->info('📊 Résumé: 1 bâtiment, 3 étages, 3 salles, 30 étagères, 300 boîtes d\'archives');
    }

    /**
     * Ajouter des contacts par défaut à une organisation
     */
    private function addDefaultContacts(Organisation $org): void
    {
        $code = strtolower($org->code);

        $contacts = [
            [
                'type' => 'email',
                'value' => $code . '@example.com',
                'label' => 'Email principal',
                'notes' => null,
            ],
            [
                'type' => 'telephone',
                'value' => '+237 650000000',
                'label' => 'Standard',
                'notes' => null,
            ],
            [
                'type' => 'adresse',
                'value' => 'Adresse de la ' . $org->name,
                'label' => 'Siège',
                'notes' => null,
            ],
            [
                'type' => 'code_postal',
                'value' => 'BP 12345',
                'label' => null,
                'notes' => null,
            ],
        ];

        foreach ($contacts as $data) {
            $contact = Contact::create($data);
            $org->contacts()->attach($contact->id);
        }
    }
}
