<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordArtifact;
use App\Models\RecordArtifactExhibition;
use App\Models\RecordArtifactLoan;
use App\Models\RecordArtifactConditionReport;
use App\Models\User;
use App\Models\Organisation;
use App\Services\RecordArtifactService;

class MigrateArtifactsSeeder extends Seeder
{
    private RecordArtifactService $service;
    private User $creator;
    private Organisation $organisation;

    public function __construct()
    {
        $this->service = new RecordArtifactService();
    }

    public function run(): void
    {
        echo "\n🏛️  Création des artifacts de musée (Phase 6)...\n\n";

        // Récupérer utilisateur et organisation
        $this->creator = User::first();
        $this->organisation = Organisation::first();

        if (!$this->creator || !$this->organisation) {
            echo "❌ ERREUR: Utilisateur ou Organisation introuvable\n";
            return;
        }

        // Nettoyage
        echo "🧹 Nettoyage des artifacts existants...\n";
        RecordArtifactConditionReport::query()->delete();
        RecordArtifactLoan::query()->delete();
        RecordArtifactExhibition::query()->delete();
        RecordArtifact::query()->forceDelete();
        echo "   ✓ Artifacts précédents supprimés\n\n";

        // Créer les artifacts
        $artifacts = [];

        echo "🎨 Création des artifacts...\n";
        $artifacts['vase_ming'] = $this->createVaseMing();
        $artifacts['statue_egyptienne'] = $this->createStatueEgyptienne();
        $artifacts['tableau_renaissance'] = $this->createTableauRenaissance();
        $artifacts['armure_medievale'] = $this->createArmureMedievale();
        $artifacts['ceramique_grecque'] = $this->createCeramiqueGrecque();
        $artifacts['sculpture_rodin'] = $this->createSculptureRodin();
        $artifacts['bijou_art_deco'] = $this->createBijouArtDeco();
        $artifacts['masque_africain'] = $this->createMasqueAfricain();
        $artifacts['tapisserie_aubusson'] = $this->createTapisserieAubusson();
        $artifacts['instrument_musique'] = $this->createInstrumentMusique();
        $artifacts['fossile_dinosaure'] = $this->createFossileDinosaure();
        $artifacts['monnaie_romaine'] = $this->createMonnaieRomaine();

        echo "   ✓ " . count($artifacts) . " artifacts créés\n\n";

        // Créer des expositions
        echo "🖼️  Ajout d'expositions...\n";
        $this->addExhibitions($artifacts);

        // Créer des prêts
        echo "📦 Ajout de prêts...\n";
        $this->addLoans($artifacts);

        // Créer des rapports de conservation
        echo "🔍 Ajout de rapports de conservation...\n";
        $this->addConditionReports($artifacts);

        // Statistiques finales
        $stats = $this->service->getStatistics($this->organisation->id);
        echo "\n✅ Seed terminé!\n";
        echo "   🏺 Total artifacts: {$stats['total']}\n";
        echo "   🖼️  En exposition: {$stats['on_display']}\n";
        echo "   📦 En prêt: {$stats['on_loan']}\n";
        echo "   🔧 En restauration: {$stats['in_restoration']}\n";
        echo "   📍 En réserve: {$stats['in_storage']}\n";
        echo "   💰 Valeur totale: " . number_format($stats['total_value'], 2) . " €\n";
        echo "   🛡️  Valeur assurance: " . number_format($stats['total_insurance_value'], 2) . " €\n";
        echo "\n🎉 Phase 6 (Museum Artifacts) terminée avec succès!\n\n";
    }

    private function createVaseMing(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Vase bleu et blanc de la dynastie Ming',
            'description' => 'Vase en porcelaine décoré de dragons et de motifs floraux. Période Wanli.',
            'category' => 'ceramique',
            'sub_category' => 'porcelaine',
            'material' => 'Porcelaine',
            'technique' => 'Décor bleu de cobalt sous couverte',
            'height' => 45.5,
            'width' => 28.0,
            'depth' => 28.0,
            'weight' => 3.2,
            'origin' => 'Jingdezhen, Chine',
            'period' => 'Dynastie Ming',
            'date_start' => 1573,
            'date_end' => 1620,
            'date_precision' => 'circa',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2015-03-20',
            'acquisition_price' => 125000.00,
            'acquisition_source' => 'Maison de ventes Sotheby\'s Paris',
            'conservation_state' => 'excellent',
            'conservation_notes' => 'Excellent état, légère usure du pied',
            'current_location' => 'Galerie Arts Asiatiques, Vitrine 3',
            'storage_location' => 'Réserve A, Étagère 12-B',
            'estimated_value' => 150000.00,
            'insurance_value' => 180000.00,
            'valuation_date' => '2024-01-15',
            'status' => 'active',
            'metadata' => [
                'provenance' => 'Collection privée européenne',
                'bibliographie' => 'Catalogue Sotheby\'s 2015, lot 234',
                'exposition_historique' => 'Exposition temporaire "Trésors de Chine", 2018',
            ],
        ], $this->creator, $this->organisation);
    }

    private function createStatueEgyptienne(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Statuette funéraire d\'Osiris',
            'description' => 'Figurine en bronze représentant le dieu Osiris avec la couronne Atef',
            'category' => 'sculpture',
            'sub_category' => 'statuaire religieuse',
            'material' => 'Bronze',
            'technique' => 'Fonte à la cire perdue',
            'height' => 18.5,
            'width' => 5.2,
            'depth' => 4.8,
            'weight' => 0.420,
            'origin' => 'Égypte, probablement Thèbes',
            'period' => 'Basse Époque',
            'date_start' => -664,
            'date_end' => -332,
            'date_precision' => 'circa',
            'author' => 'Anonyme',
            'acquisition_method' => 'don',
            'acquisition_date' => '1992-11-08',
            'acquisition_source' => 'Don de M. Jean Durand, égyptologue',
            'conservation_state' => 'good',
            'conservation_notes' => 'Patine verte caractéristique, quelques concrétions',
            'current_location' => 'Galerie Égyptienne, Vitrine 7',
            'estimated_value' => 8500.00,
            'insurance_value' => 12000.00,
            'valuation_date' => '2023-06-10',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createTableauRenaissance(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Portrait de dame à la licorne',
            'description' => 'Huile sur panneau de chêne représentant une dame noble avec une licorne en arrière-plan',
            'category' => 'peinture',
            'sub_category' => 'portrait',
            'material' => 'Huile sur panneau de chêne',
            'technique' => 'Peinture à l\'huile, technique flamande',
            'height' => 82.0,
            'width' => 64.5,
            'depth' => 3.5,
            'weight' => 8.5,
            'origin' => 'Flandres',
            'period' => 'Renaissance flamande',
            'date_start' => 1520,
            'date_end' => 1540,
            'date_precision' => 'circa',
            'author' => 'Attribué au Maître de la Légende de Sainte Ursule',
            'author_role' => 'peintre',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2008-05-12',
            'acquisition_price' => 285000.00,
            'acquisition_source' => 'Galerie Canesso, Paris',
            'conservation_state' => 'good',
            'conservation_notes' => 'Restauré en 2009, quelques repeints anciens',
            'last_conservation_check' => '2023-09-15',
            'next_conservation_check' => '2025-09-15',
            'current_location' => 'Salle Renaissance, Mur Est',
            'estimated_value' => 350000.00,
            'insurance_value' => 420000.00,
            'valuation_date' => '2024-02-01',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createArmureMedievale(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Armure de chevalier du XVe siècle',
            'description' => 'Armure complète de plate en acier forgé avec heaume',
            'category' => 'armes_armures',
            'sub_category' => 'armure de combat',
            'material' => 'Acier forgé',
            'technique' => 'Forgeage, assemblage par rivets',
            'height' => 175.0,
            'width' => 60.0,
            'depth' => 45.0,
            'weight' => 28.500,
            'origin' => 'Milan, Italie',
            'period' => 'Renaissance',
            'date_start' => 1450,
            'date_end' => 1470,
            'author' => 'Atelier de la famille Missaglia',
            'author_role' => 'armuriers milanais',
            'acquisition_method' => 'achat',
            'acquisition_date' => '1998-10-22',
            'acquisition_price' => 165000.00,
            'conservation_state' => 'fair',
            'conservation_notes' => 'Rouille superficielle sur certaines pièces, manque le gantelet droit',
            'last_conservation_check' => '2024-01-10',
            'next_conservation_check' => '2025-01-10',
            'current_location' => 'Salle des Armes',
            'estimated_value' => 220000.00,
            'insurance_value' => 250000.00,
            'valuation_date' => '2023-11-20',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createCeramiqueGrecque(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Cratère à figures rouges attique',
            'description' => 'Vase à vin décoré d\'une scène de banquet',
            'category' => 'ceramique',
            'sub_category' => 'céramique grecque',
            'material' => 'Terre cuite',
            'technique' => 'Figures rouges sur fond noir',
            'height' => 38.5,
            'width' => 42.0,
            'weight' => 2.8,
            'origin' => 'Athènes, Grèce',
            'period' => 'Période classique',
            'date_start' => -480,
            'date_end' => -460,
            'author' => 'Peintre de Berlin',
            'author_role' => 'céramiste',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2010-06-15',
            'acquisition_price' => 95000.00,
            'conservation_state' => 'good',
            'conservation_notes' => 'Restauré en 2011, recollage de 3 fragments',
            'current_location' => 'Galerie Grecque, Vitrine centrale',
            'estimated_value' => 120000.00,
            'insurance_value' => 145000.00,
            'valuation_date' => '2023-08-05',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createSculptureRodin(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Le Penseur (bronze)',
            'description' => 'Fonte en bronze de la célèbre sculpture d\'Auguste Rodin',
            'category' => 'sculpture',
            'sub_category' => 'sculpture moderne',
            'material' => 'Bronze',
            'technique' => 'Fonte au sable, patine brune',
            'height' => 71.5,
            'width' => 40.0,
            'depth' => 58.0,
            'weight' => 95.500,
            'origin' => 'Paris, France',
            'period' => 'XIXe siècle',
            'date_start' => 1902,
            'date_end' => 1902,
            'date_precision' => 'exact',
            'author' => 'Auguste Rodin',
            'author_role' => 'sculpteur',
            'author_birth_date' => '1840-11-12',
            'author_death_date' => '1917-11-17',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2012-03-18',
            'acquisition_price' => 520000.00,
            'acquisition_source' => 'Vente Christie\'s New York',
            'conservation_state' => 'excellent',
            'conservation_notes' => 'État exceptionnel, numéro de fonte 4/12',
            'last_conservation_check' => '2024-03-01',
            'next_conservation_check' => '2026-03-01',
            'current_location' => 'Hall d\'entrée principal',
            'estimated_value' => 650000.00,
            'insurance_value' => 800000.00,
            'valuation_date' => '2024-03-15',
            'status' => 'active',
            'metadata' => [
                'edition' => '4/12',
                'cachet' => 'A. Rodin',
                'fondeur' => 'Fonderie Alexis Rudier',
            ],
        ], $this->creator, $this->organisation);
    }

    private function createBijouArtDeco(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Broche Art Déco diamants et émeraudes',
            'description' => 'Broche en platine sertie de diamants taille brillant et émeraudes',
            'category' => 'bijoux',
            'sub_category' => 'joaillerie',
            'material' => 'Platine, diamants, émeraudes',
            'technique' => 'Sertissage clos et griffes',
            'height' => 6.5,
            'width' => 4.2,
            'depth' => 1.2,
            'weight' => 0.025,
            'origin' => 'Paris, France',
            'period' => 'Art Déco',
            'date_start' => 1925,
            'date_end' => 1928,
            'author' => 'Maison Cartier',
            'author_role' => 'joaillier',
            'acquisition_method' => 'don',
            'acquisition_date' => '2019-09-10',
            'acquisition_source' => 'Legs de Mme Henriette Dubois',
            'conservation_state' => 'excellent',
            'conservation_notes' => 'État parfait, poinçons lisibles',
            'current_location' => 'Vitrine Bijoux, Salle Art Déco',
            'estimated_value' => 45000.00,
            'insurance_value' => 55000.00,
            'valuation_date' => '2023-10-01',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createMasqueAfricain(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Masque cérémoniel Dan',
            'description' => 'Masque en bois sculpté utilisé lors de cérémonies d\'initiation',
            'category' => 'ethnographie',
            'sub_category' => 'art africain',
            'material' => 'Bois (fromager), pigments naturels',
            'technique' => 'Sculpture au couteau, polissage',
            'height' => 28.0,
            'width' => 17.5,
            'depth' => 12.0,
            'weight' => 0.650,
            'origin' => 'Côte d\'Ivoire / Liberia',
            'period' => 'XXe siècle',
            'date_start' => 1920,
            'date_end' => 1940,
            'date_precision' => 'circa',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2005-11-22',
            'acquisition_price' => 12000.00,
            'conservation_state' => 'fair',
            'conservation_notes' => 'Fissures anciennes stabilisées, pigments partiellement effacés',
            'last_conservation_check' => '2023-05-15',
            'next_conservation_check' => '2024-11-15',
            'current_location' => 'Salle Arts d\'Afrique',
            'estimated_value' => 18000.00,
            'insurance_value' => 22000.00,
            'valuation_date' => '2023-06-01',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createTapisserieAubusson(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Tapisserie d\'Aubusson "Les Chasses de Louis XV"',
            'description' => 'Tapisserie en laine et soie représentant une scène de chasse à courre',
            'category' => 'textile',
            'sub_category' => 'tapisserie',
            'material' => 'Laine, soie',
            'technique' => 'Tapisserie de basse lisse',
            'height' => 285.0,
            'width' => 340.0,
            'origin' => 'Aubusson, France',
            'period' => 'XVIIIe siècle',
            'date_start' => 1730,
            'date_end' => 1745,
            'acquisition_method' => 'achat',
            'acquisition_date' => '2001-04-10',
            'acquisition_price' => 175000.00,
            'conservation_state' => 'poor',
            'conservation_notes' => 'Usure importante, trous, décoloration',
            'last_conservation_check' => '2024-02-01',
            'next_conservation_check' => '2024-08-01',
            'current_location' => 'Atelier de restauration',
            'estimated_value' => 140000.00,
            'insurance_value' => 180000.00,
            'valuation_date' => '2023-12-10',
            'status' => 'in_restoration',
        ], $this->creator, $this->organisation);
    }

    private function createInstrumentMusique(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Violon Antonio Stradivarius',
            'description' => 'Violon de concert réalisé dans l\'atelier Stradivarius',
            'category' => 'instruments_musique',
            'sub_category' => 'cordes frottées',
            'material' => 'Épicéa, érable, ébène',
            'technique' => 'Lutherie italienne',
            'height' => 59.5,
            'width' => 20.5,
            'depth' => 11.8,
            'weight' => 0.450,
            'origin' => 'Crémone, Italie',
            'period' => 'Période baroque',
            'date_start' => 1715,
            'date_end' => 1715,
            'date_precision' => 'exact',
            'author' => 'Antonio Stradivari',
            'author_role' => 'luthier',
            'author_birth_date' => '1644-01-01',
            'author_death_date' => '1737-12-18',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2017-10-05',
            'acquisition_price' => 2850000.00,
            'acquisition_source' => 'Auction Tarisio',
            'conservation_state' => 'excellent',
            'conservation_notes' => 'Condition exceptionnelle, certificat d\'authenticité',
            'last_conservation_check' => '2024-06-01',
            'next_conservation_check' => '2025-06-01',
            'current_location' => 'Vitrine sécurisée, Galerie Instruments',
            'estimated_value' => 3200000.00,
            'insurance_value' => 3800000.00,
            'valuation_date' => '2024-01-10',
            'status' => 'active',
            'metadata' => [
                'label' => 'Antonius Stradivarius Cremonensis Faciebat Anno 1715',
                'certificat' => 'Expert J. & A. Beare, Londres, 2017',
            ],
        ], $this->creator, $this->organisation);
    }

    private function createFossileDinosaure(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Crâne de Triceratops',
            'description' => 'Crâne fossile partiel de dinosaure herbivore du Crétacé',
            'category' => 'histoire_naturelle',
            'sub_category' => 'paléontologie',
            'material' => 'Os fossilisés',
            'height' => 95.0,
            'width' => 180.0,
            'depth' => 85.0,
            'weight' => 125.000,
            'origin' => 'Montana, États-Unis',
            'period' => 'Crétacé supérieur',
            'date_start' => -68000000,
            'date_end' => -66000000,
            'date_precision' => 'circa',
            'acquisition_method' => 'achat',
            'acquisition_date' => '2016-07-18',
            'acquisition_price' => 320000.00,
            'acquisition_source' => 'Black Hills Institute',
            'conservation_state' => 'good',
            'conservation_notes' => 'Environ 65% de l\'original, restaurations bien intégrées',
            'current_location' => 'Hall Paléontologie',
            'estimated_value' => 380000.00,
            'insurance_value' => 450000.00,
            'valuation_date' => '2023-09-01',
            'status' => 'active',
        ], $this->creator, $this->organisation);
    }

    private function createMonnaieRomaine(): RecordArtifact
    {
        return $this->service->createArtifact([
            'name' => 'Aureus de l\'empereur Auguste',
            'description' => 'Pièce d\'or romaine à l\'effigie d\'Auguste',
            'category' => 'numismatique',
            'sub_category' => 'monnaies antiques',
            'material' => 'Or',
            'height' => 0.3,
            'width' => 1.95,
            'weight' => 0.0078,
            'origin' => 'Lyon (Lugdunum), Gaule romaine',
            'period' => 'Haut-Empire romain',
            'date_start' => -2,
            'date_end' => 4,
            'date_precision' => 'circa',
            'acquisition_method' => 'don',
            'acquisition_date' => '2020-12-05',
            'acquisition_source' => 'Don anonyme',
            'conservation_state' => 'excellent',
            'conservation_notes' => 'État de conservation exceptionnel, très beau style',
            'current_location' => 'Cabinet des Médailles',
            'estimated_value' => 15000.00,
            'insurance_value' => 18000.00,
            'valuation_date' => '2023-07-15',
            'status' => 'active',
            'metadata' => [
                'atelier' => 'Lyon',
                'revers' => 'Caius et Lucius Caesar',
                'poids' => '7.8g',
                'reference' => 'RIC 207',
            ],
        ], $this->creator, $this->organisation);
    }

    private function addExhibitions(array $artifacts): void
    {
        // Exposition actuelle: Trésors d'Asie
        $this->service->addToExhibition($artifacts['vase_ming'], [
            'exhibition_name' => 'Trésors d\'Asie',
            'venue' => 'Galerie temporaire du musée',
            'start_date' => '2024-09-01',
            'end_date' => '2025-02-28',
        ], true);

        $this->service->addToExhibition($artifacts['ceramique_grecque'], [
            'exhibition_name' => 'Trésors d\'Asie',
            'venue' => 'Galerie temporaire du musée',
            'start_date' => '2024-09-01',
            'end_date' => '2025-02-28',
        ], true);

        // Exposition permanente: Renaissance
        $this->service->addToExhibition($artifacts['tableau_renaissance'], [
            'exhibition_name' => 'Collection permanente Renaissance',
            'venue' => 'Salle Renaissance',
            'start_date' => '2020-01-01',
            'end_date' => null,
        ], true);

        // Exposition passée
        RecordArtifactExhibition::create([
            'artifact_id' => $artifacts['sculpture_rodin']->id,
            'exhibition_name' => 'Rodin et ses contemporains',
            'venue' => 'Musée national',
            'start_date' => '2023-03-15',
            'end_date' => '2023-09-30',
            'is_current' => false,
        ]);

        echo "   ✓ " . RecordArtifactExhibition::count() . " expositions créées\n";
    }

    private function addLoans(array $artifacts): void
    {
        // Prêt actif: Violon Stradivarius
        $this->service->loanArtifact($artifacts['instrument_musique'], [
            'borrower_name' => 'Orchestre Philharmonique de Paris',
            'borrower_contact' => 'contact@orchestreparis.fr',
            'loan_date' => '2024-10-01',
            'return_date' => '2025-03-31',
            'conditions' => 'Assurance tous risques obligatoire. Conservation en climat contrôlé.',
            'notes' => 'Prêt pour la saison de concerts 2024-2025',
        ]);

        // Prêt retourné
        $loan = RecordArtifactLoan::create([
            'artifact_id' => $artifacts['bijou_art_deco']->id,
            'borrower_name' => 'Musée des Arts Décoratifs',
            'borrower_contact' => 'prets@madparis.fr',
            'loan_date' => '2023-06-01',
            'return_date' => '2023-12-15',
            'actual_return_date' => '2023-12-10',
            'status' => 'returned',
            'conditions' => 'Vitrine sécurisée',
            'notes' => 'Exposition "L\'Art Déco au quotidien"',
        ]);

        echo "   ✓ " . RecordArtifactLoan::count() . " prêts créés\n";
    }

    private function addConditionReports(array $artifacts): void
    {
        // Rapports de conservation
        $this->service->addConditionReport($artifacts['tapisserie_aubusson'], [
            'report_date' => '2024-02-01',
            'overall_condition' => 'poor',
            'observations' => 'Usure généralisée du textile. Multiples trous et déchirures. Décoloration importante des rouges et jaunes.',
            'recommendations' => 'Restauration urgente nécessaire. Travail estimé à 6-8 mois. Budget: 35000€',
            'next_conservation_check' => '2024-08-01',
        ], $this->creator);

        $this->service->addConditionReport($artifacts['armure_medievale'], [
            'report_date' => '2024-01-10',
            'overall_condition' => 'fair',
            'observations' => 'Oxydation superficielle généralisée. Manque le gantelet droit. Rivets en bon état.',
            'recommendations' => 'Traitement anti-corrosion recommandé. Recherche du gantelet manquant.',
            'next_conservation_check' => '2025-01-10',
        ], $this->creator);

        $this->service->addConditionReport($artifacts['masque_africain'], [
            'report_date' => '2023-05-15',
            'overall_condition' => 'fair',
            'observations' => 'Fissures anciennes stabilisées lors de la restauration 2006. Pigments effacés à 40%.',
            'recommendations' => 'Surveillance régulière. Éviter exposition directe à la lumière.',
            'next_conservation_check' => '2024-11-15',
        ], $this->creator);

        $this->service->addConditionReport($artifacts['vase_ming'], [
            'report_date' => '2024-03-01',
            'overall_condition' => 'excellent',
            'observations' => 'Aucune dégradation visible. Décor en parfait état.',
            'recommendations' => 'Maintenir les conditions actuelles de conservation.',
            'next_conservation_check' => '2026-03-01',
        ], $this->creator);

        $this->service->addConditionReport($artifacts['sculpture_rodin'], [
            'report_date' => '2024-03-01',
            'overall_condition' => 'excellent',
            'observations' => 'Patine homogène et stable. Aucune altération.',
            'recommendations' => 'Dépoussiérage régulier au pinceau doux.',
            'next_conservation_check' => '2026-03-01',
        ], $this->creator);

        echo "   ✓ " . RecordArtifactConditionReport::count() . " rapports de conservation créés\n";
    }
}
