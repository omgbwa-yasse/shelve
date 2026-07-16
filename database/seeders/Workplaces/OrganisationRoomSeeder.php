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
            $this->command->info('🏢 Affectation des salles aux organisations...');

            // Supprimer les relations existantes
            DB::table('organisation_room')->delete();

            // Récupérer toutes les directions principales (enfants de DG)
            $directionGenerale = Organisation::where('code', 'DG')->first();
            if (!$directionGenerale) {
                $this->command->error('Direction Générale non trouvée');
                return;
            }

            $directions = Organisation::where('parent_id', $directionGenerale->id)->get();

            foreach ($directions as $direction) {
                $this->command->info("📍 Traitement de la direction: {$direction->name}");

                // Trouver la salle correspondante à cette direction
                $room = $this->findRoomForDirection($direction);

                if ($room) {
                    // Affecter la salle à la direction elle-même
                    $this->attachRoomToOrganisation($room, $direction);

                    // Récupérer tous les services et bureaux de cette direction (descendants)
                    $descendants = $this->getAllDescendants($direction);

                    foreach ($descendants as $descendant) {
                        $this->attachRoomToOrganisation($room, $descendant);
                        $this->command->line("   ✅ Salle '{$room->name}' affectée à '{$descendant->name}'");
                    }

                    $totalOrganisations = $descendants->count() + 1;
                    $this->command->info("✅ {$totalOrganisations} organisations affectées à la salle '{$room->name}'");
                } else {
                    $this->command->warn("⚠️ Aucune salle trouvée pour la direction {$direction->name}");
                }
            }

            DB::commit();
            $this->command->info('✅ Affectation des salles aux organisations terminée avec succès');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Erreur lors de l\'affectation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trouver la salle correspondante à une direction
     */
    private function findRoomForDirection($direction)
    {
        // Mapping des directions vers les codes de salles
        $roomMappings = [
            'DSI' => 'SALLE-DSI',
            'DRH' => 'SALLE-DRH',
            'DAG' => 'SALLE-DAG',
        ];

        $roomCode = $roomMappings[$direction->code] ?? null;

        if ($roomCode) {
            return Room::where('code', $roomCode)->first();
        }

        return null;
    }

    /**
     * Récupérer tous les descendants d'une organisation (récursif)
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
     * Affecter une salle à une organisation (éviter les doublons)
     */
    private function attachRoomToOrganisation($room, $organisation)
    {
        // Vérifier si la relation existe déjà
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

