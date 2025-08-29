<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class OrganisationServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('🏢 Création des services et bureaux...');

            // Récupérer les directions
            $directionFinances = Organisation::where('code', 'DF')->first();
            $directionRH = Organisation::where('code', 'DRH')->first();
            $directionArchives = Organisation::where('code', 'DADA')->first();

            if (!$directionFinances || !$directionRH || !$directionArchives) {
                $this->command->error('Les directions principales doivent être créées avant ce seeder');
                return;
            }

            // Créer les services pour la Direction des Finances
            $this->createFinanceServices($directionFinances);

            // Créer les services pour la Direction des Ressources Humaines
            $this->createHRServices($directionRH);

            // Créer les services pour la Direction des Archives
            $this->createArchiveServices($directionArchives);

            DB::commit();
            $this->command->info('✅ Services et bureaux créés avec succès');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Erreur lors de la création: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer les services de la Direction des Finances
     */
    private function createFinanceServices($directionFinances)
    {
        $this->command->info('💰 Création des services - Direction des Finances...');

        // Service Comptabilité
        $serviceComptabilite = Organisation::create([
            'code' => 'DF-COMPTA',
            'name' => 'Service Comptabilité',
            'description' => 'Service en charge de la comptabilité générale',
            'parent_id' => $directionFinances->id
        ]);

        // Bureaux du Service Comptabilité
        Organisation::create([
            'code' => 'DF-CG',
            'name' => 'Bureau Comptabilité Générale',
            'description' => 'Bureau de la comptabilité générale',
            'parent_id' => $serviceComptabilite->id
        ]);

        Organisation::create([
            'code' => 'DF-BUDGET',
            'name' => 'Bureau Budget',
            'description' => 'Bureau de gestion budgétaire',
            'parent_id' => $serviceComptabilite->id
        ]);

        // Service Trésorerie
        $serviceTresorerie = Organisation::create([
            'code' => 'DF-TRES',
            'name' => 'Service Trésorerie',
            'description' => 'Service en charge de la trésorerie',
            'parent_id' => $directionFinances->id
        ]);

        // Bureau du Service Trésorerie
        Organisation::create([
            'code' => 'DF-CAISSE',
            'name' => 'Bureau Caisse',
            'description' => 'Bureau de gestion de la caisse',
            'parent_id' => $serviceTresorerie->id
        ]);

        $this->command->info('   ✅ 5 organisations créées pour la Direction des Finances');
    }

    /**
     * Créer les services de la Direction des Ressources Humaines
     */
    private function createHRServices($directionRH)
    {
        $this->command->info('👥 Création des services - Direction des Ressources Humaines...');

        // Service Recrutement
        $serviceRecrutement = Organisation::create([
            'code' => 'DRH-REC',
            'name' => 'Service Recrutement',
            'description' => 'Service en charge du recrutement',
            'parent_id' => $directionRH->id
        ]);

        // Bureau du Service Recrutement
        Organisation::create([
            'code' => 'DRH-SEL',
            'name' => 'Bureau Sélection',
            'description' => 'Bureau de sélection des candidats',
            'parent_id' => $serviceRecrutement->id
        ]);

        // Service Formation
        $serviceFormation = Organisation::create([
            'code' => 'DRH-FORM',
            'name' => 'Service Formation',
            'description' => 'Service en charge de la formation',
            'parent_id' => $directionRH->id
        ]);

        // Bureau du Service Formation
        Organisation::create([
            'code' => 'DRH-PLAN',
            'name' => 'Bureau Planification Formation',
            'description' => 'Bureau de planification des formations',
            'parent_id' => $serviceFormation->id
        ]);

        // Service Paie
        Organisation::create([
            'code' => 'DRH-PAIE',
            'name' => 'Service Paie',
            'description' => 'Service en charge de la paie',
            'parent_id' => $directionRH->id
        ]);

        $this->command->info('   ✅ 5 organisations créées pour la Direction des Ressources Humaines');
    }

    /**
     * Créer les services de la Direction des Archives
     */
    private function createArchiveServices($directionArchives)
    {
        $this->command->info('📚 Création des services - Direction des Archives...');

        // Service Classement
        $serviceClassement = Organisation::create([
            'code' => 'DADA-CL',
            'name' => 'Service Classement',
            'description' => 'Service en charge du classement des archives',
            'parent_id' => $directionArchives->id
        ]);

        // Bureau du Service Classement
        Organisation::create([
            'code' => 'DADA-COT',
            'name' => 'Bureau Cotation',
            'description' => 'Bureau de cotation des archives',
            'parent_id' => $serviceClassement->id
        ]);

        // Service Conservation
        $serviceConservation = Organisation::create([
            'code' => 'DADA-CON',
            'name' => 'Service Conservation',
            'description' => 'Service en charge de la conservation',
            'parent_id' => $directionArchives->id
        ]);

        // Bureau du Service Conservation
        Organisation::create([
            'code' => 'DADA-PREV',
            'name' => 'Bureau Conservation Préventive',
            'description' => 'Bureau de conservation préventive',
            'parent_id' => $serviceConservation->id
        ]);

        // Service Communication
        Organisation::create([
            'code' => 'DADA-COMM',
            'name' => 'Service Communication',
            'description' => 'Service en charge de la communication des archives',
            'parent_id' => $directionArchives->id
        ]);

        $this->command->info('   ✅ 5 organisations créées pour la Direction des Archives');
    }
}
