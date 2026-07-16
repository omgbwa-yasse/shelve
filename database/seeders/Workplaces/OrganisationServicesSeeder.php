<?php

namespace Database\Seeders\Workplaces;

use Illuminate\Database\Seeder;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

/**
 * Services et bureaux rattachés aux directions (DSI, DRH, DAG).
 *
 * Le « Service Courrier & Accueil » (DAG-COUR) est le point d'entrée obligatoire
 * de tout courrier externe : il est déposé à l'accueil avant de monter au
 * secrétariat du DG pour cotation.
 */
class OrganisationServicesSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('Création des services et bureaux...');

            $directionSI = Organisation::where('code', 'DSI')->first();
            $directionRH = Organisation::where('code', 'DRH')->first();
            $directionAG = Organisation::where('code', 'DAG')->first();

            if (!$directionSI || !$directionRH || !$directionAG) {
                $this->command->error('Les directions principales doivent être créées avant ce seeder');
                return;
            }

            $this->createSIServices($directionSI);
            $this->createHRServices($directionRH);
            $this->createAGServices($directionAG);

            DB::commit();
            $this->command->info('Services et bureaux créés avec succès');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Erreur lors de la création: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Services de la Direction des Systèmes d'Information
     */
    private function createSIServices($directionSI): void
    {
        $this->command->info('Création des services - Direction des Systèmes d\'Information...');

        $serviceInfra = Organisation::create([
            'code' => 'DSI-INFRA',
            'name' => 'Service Infrastructures',
            'description' => 'Service en charge des infrastructures et des réseaux',
            'parent_id' => $directionSI->id,
        ]);

        Organisation::create([
            'code' => 'DSI-RES',
            'name' => 'Bureau Réseaux',
            'description' => 'Bureau en charge des réseaux et télécommunications',
            'parent_id' => $serviceInfra->id,
        ]);

        Organisation::create([
            'code' => 'DSI-APP',
            'name' => 'Service Applications',
            'description' => 'Service en charge des applications métier',
            'parent_id' => $directionSI->id,
        ]);

        Organisation::create([
            'code' => 'DSI-SUP',
            'name' => 'Service Support',
            'description' => 'Service en charge du support aux utilisateurs',
            'parent_id' => $directionSI->id,
        ]);

        $this->command->info('   4 organisations créées pour la DSI');
    }

    /**
     * Services de la Direction des Ressources Humaines
     */
    private function createHRServices($directionRH): void
    {
        $this->command->info('Création des services - Direction des Ressources Humaines...');

        $serviceRecrutement = Organisation::create([
            'code' => 'DRH-REC',
            'name' => 'Service Recrutement',
            'description' => 'Service en charge du recrutement',
            'parent_id' => $directionRH->id,
        ]);

        Organisation::create([
            'code' => 'DRH-SEL',
            'name' => 'Bureau Sélection',
            'description' => 'Bureau de sélection des candidats',
            'parent_id' => $serviceRecrutement->id,
        ]);

        $serviceFormation = Organisation::create([
            'code' => 'DRH-FORM',
            'name' => 'Service Formation',
            'description' => 'Service en charge de la formation',
            'parent_id' => $directionRH->id,
        ]);

        Organisation::create([
            'code' => 'DRH-PLAN',
            'name' => 'Bureau Planification Formation',
            'description' => 'Bureau de planification des formations',
            'parent_id' => $serviceFormation->id,
        ]);

        Organisation::create([
            'code' => 'DRH-PAIE',
            'name' => 'Service Paie',
            'description' => 'Service en charge de la paie',
            'parent_id' => $directionRH->id,
        ]);

        $this->command->info('   5 organisations créées pour la DRH');
    }

    /**
     * Services de la Direction des Affaires Générales
     */
    private function createAGServices($directionAG): void
    {
        $this->command->info('Création des services - Direction des Affaires Générales...');

        // Point d'entrée obligatoire de tout courrier externe (dépôt à l'accueil).
        Organisation::create([
            'code' => 'DAG-COUR',
            'name' => 'Service Courrier & Accueil',
            'description' => "Accueil : point de dépôt obligatoire de tout courrier externe entrant",
            'parent_id' => $directionAG->id,
        ]);

        Organisation::create([
            'code' => 'DAG-ARCH',
            'name' => 'Service Archives',
            'description' => 'Service en charge de la gestion des archives et documents administratifs',
            'parent_id' => $directionAG->id,
        ]);

        Organisation::create([
            'code' => 'DAG-LOG',
            'name' => 'Service Logistique',
            'description' => 'Service en charge de la logistique et des moyens généraux',
            'parent_id' => $directionAG->id,
        ]);

        Organisation::create([
            'code' => 'DAG-PAT',
            'name' => 'Service Patrimoine',
            'description' => 'Service en charge du patrimoine et des équipements',
            'parent_id' => $directionAG->id,
        ]);

        $this->command->info('   4 organisations créées pour la DAG');
    }
}
