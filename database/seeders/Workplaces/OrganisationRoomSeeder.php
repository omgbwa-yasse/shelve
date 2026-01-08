<?php

namespace Database\Seeders\Workplaces;

use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class OrganisationRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('ðŸ¢ Affectation des salles aux organisations...');

            // Supprimer les relations existantes
            DB::table('organisation_room')->delete();

            // RÃ©cupÃ©rer toutes les directions principales (enfants de DG)
            $directionGenerale = Organisation::where('code', 'DG')->first();
            if (!$directionGenerale) {
                $this->command->error('Direction GÃ©nÃ©rale non trouvÃ©e');
                return;
            }

            $directions = Organisation::where('parent_id', $directionGenerale->id)->get();

            foreach ($directions as $direction) {
                $this->command->info("ðŸ“ Traitement de la direction: {$direction->name}");

                // Trouver la salle correspondante Ã  cette direction
                $room = $this->findRoomForDirection($direction);

                if ($room) {
                    // Affecter la salle Ã  la direction elle-mÃªme
                    $this->attachRoomToOrganisation($room, $direction);

                    // RÃ©cupÃ©rer tous les services et bureaux de cette direction (descendants)
                    $descendants = $this->getAllDescendants($direction);

                    foreach ($descendants as $descendant) {
                        $this->attachRoomToOrganisation($room, $descendant);
                        $this->command->line("   âœ… Salle '{$room->name}' affectÃ©e Ã  '{$descendant->name}'");
                    }

                    $totalOrganisations = $descendants->count() + 1;
                    $this->command->info("âœ… {$totalOrganisations} organisations affectÃ©es Ã  la salle '{$room->name}'");
                } else {
                    $this->command->warn("âš ï¸ Aucune salle trouvÃ©e pour la direction {$direction->name}");
                }
            }

            DB::commit();
            $this->command->info('âœ… Affectation des salles aux organisations terminÃ©e avec succÃ¨s');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('âŒ Erreur lors de l\'affectation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trouver la salle correspondante Ã  une direction
     */
    private function findRoomForDirection($direction)
    {
        // Mapping des directions vers les codes de salles
        $roomMappings = [
            'DF' => 'SALLE-DF',
            'DRH' => 'SALLE-DRH',
            'DADA' => 'SALLE-DADA'
        ];

        $roomCode = $roomMappings[$direction->code] ?? null;

        if ($roomCode) {
            return Room::where('code', $roomCode)->first();
        }

        return null;
    }

    /**
     * RÃ©cupÃ©rer tous les descendants d'une organisation (rÃ©cursif)
     */
    private function getAllDescendants($organisation)
    {
        $descendants = collect();

        $children = Organisation::where('parent_id', $organisation->id)->get();

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->getAllDescendants($child));
        }

        return $descendants;
    }

    /**
     * Affecter une salle Ã  une organisation (Ã©viter les doublons)
     */
    private function attachRoomToOrganisation($room, $organisation)
    {
        // VÃ©rifier si la relation existe dÃ©jÃ 
        $exists = DB::table('organisation_room')
                    ->where('room_id', $room->id)
                    ->where('organisation_id', $organisation->id)
                    ->exists();

        if (!$exists) {
            DB::table('organisation_room')->insert([
                'room_id' => $room->id,
                'organisation_id' => $organisation->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}

