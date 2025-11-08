<?php

namespace Database\Seeders;

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
            'RH', 'Recrutement', 'Formation', 'Paie', 'Contrat', 'Salaire', 'Employé',
            'Candidature', 'Entretien', 'Compétences', 'Évaluation', 'Carrière',

            // Comptabilité
            'Comptabilité', 'Facturation', 'Budget', 'Dépenses', 'Recettes', 'Finances',
            'Fournisseur', 'Client', 'Paiement', 'Trésorerie', 'Bilan', 'Compte',

            // Projets
            'Projet', 'Analyse', 'Étude', 'Rapport', 'Planification', 'Suivi',
            'Livrable', 'Jalon', 'Risque', 'Budget projet', 'Équipe',

            // Documentation
            'Documentation', 'Archive', 'Numérique', 'Digital', 'Document',
            'Dossier', 'Classement', 'Conservation', 'Gestion documentaire',

            // Général
            'Transformation', 'Innovation', 'Stratégie', 'Management', 'Processus',
            'Workflow', 'Qualité', 'Performance', 'Amélioration', 'Optimisation',

            // Méthodologie
            'Méthodologie', 'Procédure', 'Norme', 'Standard', 'Référentiel',
            'Guide', 'Manuel', 'Instruction', 'Consigne',

            // Communication
            'Communication', 'Information', 'Diffusion', 'Publication', 'Notification',
            'Annonce', 'Communiqué', 'Note de service',

            // Juridique
            'Juridique', 'Légal', 'Réglementaire', 'Conformité', 'Audit',
            'Contrôle', 'Vérification', 'Certification',
        ];

        foreach ($keywords as $name) {
            Keyword::firstOrCreate(['name' => $name]);
        }

        $this->command->info('✅ ' . count($keywords) . ' mots-clés créés/vérifiés');
    }
}
