<?php

namespace Database\Seeders\Tools;

use App\Models\Keyword;
use Illuminate\Database\Seeder;

class KeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            // RH
            'RH', 'Recrutement', 'Formation', 'Paie', 'Contrat', 'Salaire', 'EmployÃ©',
            'Candidature', 'Entretien', 'CompÃ©tences', 'Ã‰valuation', 'CarriÃ¨re',

            // ComptabilitÃ©
            'ComptabilitÃ©', 'Facturation', 'Budget', 'DÃ©penses', 'Recettes', 'Finances',
            'Fournisseur', 'Client', 'Paiement', 'TrÃ©sorerie', 'Bilan', 'Compte',

            // Projets
            'Projet', 'Analyse', 'Ã‰tude', 'Rapport', 'Planification', 'Suivi',
            'Livrable', 'Jalon', 'Risque', 'Budget projet', 'Ã‰quipe',

            // Documentation
            'Documentation', 'Archive', 'NumÃ©rique', 'Digital', 'Document',
            'Dossier', 'Classement', 'Conservation', 'Gestion documentaire',

            // GÃ©nÃ©ral
            'Transformation', 'Innovation', 'StratÃ©gie', 'Management', 'Processus',
            'Workflow', 'QualitÃ©', 'Performance', 'AmÃ©lioration', 'Optimisation',

            // MÃ©thodologie
            'MÃ©thodologie', 'ProcÃ©dure', 'Norme', 'Standard', 'RÃ©fÃ©rentiel',
            'Guide', 'Manuel', 'Instruction', 'Consigne',

            // Communication
            'Communication', 'Information', 'Diffusion', 'Publication', 'Notification',
            'Annonce', 'CommuniquÃ©', 'Note de service',

            // Juridique
            'Juridique', 'LÃ©gal', 'RÃ©glementaire', 'ConformitÃ©', 'Audit',
            'ContrÃ´le', 'VÃ©rification', 'Certification',
        ];

        foreach ($keywords as $name) {
            Keyword::firstOrCreate(['name' => $name]);
        }

        $this->command->info('âœ… ' . count($keywords) . ' mots-clÃ©s crÃ©Ã©s/vÃ©rifiÃ©s');
    }
}

