<?php

namespace Database\Seeders\Workplaces;

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
            $this->command->info('ðŸ—‘ï¸ Suppression de l\'infrastructure physique existante...');
            Container::query()->delete();
            Shelf::query()->delete();
            Room::query()->delete();
            Floor::query()->delete();
            Building::query()->delete();
            ContainerProperty::query()->delete();

            // Supprimer tous les contacts et liaisons existants
            $this->command->info('ðŸ—‘ï¸ Suppression des contacts existants...');
            Contact::query()->delete();

            // Supprimer toutes les organisations existantes
            $this->command->info('ðŸ—‘ï¸ Suppression des organisations existantes...');
            Organisation::query()->delete();

            // CrÃ©er la nouvelle structure organisationnelle
            $this->command->info('ðŸ¢ CrÃ©ation de la nouvelle structure organisationnelle...');

            // Direction GÃ©nÃ©rale (DG) - Organisation racine
            $directionGenerale = Organisation::create([
                'code' => 'DG',
                'name' => 'Direction GÃ©nÃ©rale',
                'description' => 'Direction gÃ©nÃ©rale de l\'organisation',
                'parent_id' => null
            ]);

            // Direction des Finances (DF)
            $directionFinances = Organisation::create([
                'code' => 'DF',
                'name' => 'Direction des Finances',
                'description' => 'Direction responsable de la gestion financiÃ¨re',
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

            $this->command->info('âœ… Structure organisationnelle crÃ©Ã©e');

            // Associer des contacts par dÃ©faut Ã  chaque organisation
            foreach ([$directionGenerale, $directionFinances, $directionRH, $directionArchives] as $org) {
                $this->addDefaultContacts($org);
            }

            // CrÃ©er l'infrastructure physique
            $this->createPhysicalInfrastructure($directionFinances, $directionRH, $directionArchives);

            DB::commit();
            $this->command->info('âœ… Infrastructure organisationnelle et physique crÃ©Ã©e avec succÃ¨s');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('âŒ Erreur lors de la crÃ©ation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * CrÃ©er l'infrastructure physique
     */
    private function createPhysicalInfrastructure($directionFinances, $directionRH, $directionArchives)
    {
        $this->command->info('ðŸ—ï¸ CrÃ©ation de l\'infrastructure physique...');

        // CrÃ©er une propriÃ©tÃ© de boÃ®te par dÃ©faut
        $defaultProperty = ContainerProperty::firstOrCreate(
            ['name' => 'BoÃ®te Archive Standard'],
            [
                'name' => 'BoÃ®te Archive Standard',
                'width' => 35.0,  // 35 cm
                'length' => 25.0, // 25 cm
                'depth' => 12.0,  // 12 cm
                'creator_id' => 999999 // Valeur temporaire, sera mise Ã  jour par SuperAdminSeeder
            ]
        );

        // CrÃ©er le bÃ¢timent principal
        $building = Building::create([
            'name' => 'BÃ¢timent Principal Archives',
            'description' => 'BÃ¢timent principal pour le stockage des archives administratives',
            'visibility' => 'public',
            'creator_id' => 999999 // Valeur temporaire, sera mise Ã  jour par SuperAdminSeeder
        ]);

        $directions = [
            ['org' => $directionFinances, 'floor_num' => 1, 'room_code' => 'SALLE-DF'],
            ['org' => $directionRH, 'floor_num' => 2, 'room_code' => 'SALLE-DRH'],
            ['org' => $directionArchives, 'floor_num' => 3, 'room_code' => 'SALLE-DADA']
        ];

        foreach ($directions as $directionData) {
            // CrÃ©er l'Ã©tage
            $floor = Floor::create([
                'name' => 'Ã‰tage ' . $directionData['floor_num'],
                'description' => 'Ã‰tage dÃ©diÃ© Ã  la ' . $directionData['org']->name,
                'building_id' => $building->id,
                'creator_id' => 999999
            ]);

            // CrÃ©er la salle
            $room = Room::create([
                'code' => $directionData['room_code'],
                'name' => 'Salle ' . $directionData['org']->code,
                'description' => 'Salle d\'archives pour la ' . $directionData['org']->name,
                'visibility' => 'public',
                'type' => 'archives',
                'floor_id' => $floor->id,
                'creator_id' => 999999
            ]);

            // Associer la salle Ã  l'organisation
            $room->organisations()->attach($directionData['org']->id);

            // CrÃ©er 10 Ã©tagÃ¨res dans la salle
            for ($shelfNum = 1; $shelfNum <= 10; $shelfNum++) {
                $shelf = Shelf::create([
                    'code' => $directionData['room_code'] . '-ET' . str_pad($shelfNum, 2, '0', STR_PAD_LEFT),
                    'observation' => 'Ã‰tagÃ¨re ' . $shelfNum . ' de la salle ' . $directionData['org']->code,
                    'face' => 1, // Face numÃ©rique au lieu de 'A'
                    'ear' => 1,
                    'shelf' => $shelfNum,
                    'shelf_length' => 200, // 2 mÃ¨tres
                    'room_id' => $room->id,
                    'creator_id' => 999999
                ]);

                // CrÃ©er 10 boÃ®tes d'archives sur chaque Ã©tagÃ¨re
                for ($boxNum = 1; $boxNum <= 10; $boxNum++) {
                    Container::create([
                        'code' => $shelf->code . '-B' . str_pad($boxNum, 2, '0', STR_PAD_LEFT),
                        'shelve_id' => $shelf->id,
                        'status_id' => 1, // Statut par dÃ©faut
                        'property_id' => $defaultProperty->id, // Utiliser la propriÃ©tÃ© par dÃ©faut
                        'creator_id' => 999999,
                        'creator_organisation_id' => $directionData['org']->id
                    ]);
                }
            }

            $this->command->info('âœ… Infrastructure crÃ©Ã©e pour ' . $directionData['org']->name . ' (Ã‰tage ' . $directionData['floor_num'] . ')');
        }

        $this->command->info('âœ… Infrastructure physique complÃ¨te crÃ©Ã©e');
        $this->command->info('ðŸ“Š RÃ©sumÃ©: 1 bÃ¢timent, 3 Ã©tages, 3 salles, 30 Ã©tagÃ¨res, 300 boÃ®tes d\'archives');
    }

    /**
     * Ajouter des contacts par dÃ©faut Ã  une organisation
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
                'label' => 'SiÃ¨ge',
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

