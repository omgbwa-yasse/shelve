<?php

namespace Database\Seeders\Workplaces;

use App\Models\Organisation;
use App\Models\OrganisationInterim;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Exemple d'intérim pour la démo : le directeur de la DSI est en mission,
 * deux intérimaires se partagent ses attributions par volet.
 * L'intérimaire principal reçoit le courrier routé vers la direction.
 */
class OrganisationInterimSeeder extends Seeder
{
    public function run(): void
    {
        $dsi = Organisation::where('code', 'DSI')->first();
        $titular = User::where('email', 'dir.dsi@example.com')->first();
        $technique = User::where('email', 'resp.infra@example.com')->first();
        $administratif = User::where('email', 'resp.reseaux@example.com')->first();
        $dg = User::where('email', 'dg@example.com')->first();

        if (!$dsi || !$titular || !$technique || !$administratif) {
            $this->command?->warn('OrganisationInterimSeeder ignoré : entités ou utilisateurs de démo absents.');

            return;
        }

        // Volets délégués : rattachés si possible à une activité du plan de classement de la DSI.
        $activites = $dsi->activities()->pluck('id', 'name');
        $activiteInfra = $activites->first(fn ($id, $name) => str_contains($name, 'INFRASTRUCTURE'));
        $activiteSupport = $activites->first(fn ($id, $name) => str_contains($name, 'SUPPORT'));

        $volets = [
            [$technique, 'Volet technique (infrastructures, exploitation)', true, $activiteInfra],
            [$administratif, 'Volet administratif (budget, marchés, RH du service)', false, $activiteSupport],
        ];

        foreach ($volets as [$user, $scope, $isPrimary, $activityId]) {
            OrganisationInterim::updateOrCreate(
                [
                    'organisation_id' => $dsi->id,
                    'titular_user_id' => $titular->id,
                    'interim_user_id' => $user->id,
                ],
                [
                    'scope' => $scope,
                    'activity_id' => $activityId,
                    'is_primary' => $isPrimary,
                    'start_date' => now()->subDays(2),
                    'end_date' => now()->addDays(12),
                    'is_active' => true,
                    'reason' => 'Mission hors station du directeur',
                    'created_by' => $dg?->id,
                ]
            );
        }

        $this->command?->info('Intérims de démo créés pour la DSI (2 volets).');
    }
}
