<?php

namespace Database\Seeders;

use App\Models\AuthorType;
use Illuminate\Database\Seeder;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailStatus;
use App\Models\MailType;
use App\Models\ContainerType;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\documentType;
use App\Models\Shelf;
use App\Models\MailSubject;
use App\Models\batch;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        ContainerType::insert([
            ['name' => 'Boîte', 'description' => 'Un petit conteneur rectangulaire utilisé pour stocker et transporter des articles.'],
            ['name' => 'Chrono', 'description' => 'Un type de conteneur conçu pour les livraisons sensibles au temps, souvent utilisé pour l\'expédition de documents et de colis qui doivent arriver rapidement.'],
            ['name' => 'Liasse', 'description' => 'Une collection de documents ou de papiers liés ensemble dans un seul paquet, souvent utilisés à des fins légales ou financières.'],
            ['name' => 'Carton', 'description' => 'Un grand conteneur robuste en carton ondulé, utilisé pour l\'expédition et le stockage de marchandises.'],
            ['name' => 'Conteneur d\'archives', 'description' => 'Conteneur d\'archives Un conteneur utilisé pour stocker des documents et des objets à long terme, souvent utilisé dans les bibliothèques, les archives et les entreprises.'],
        ]);

        RecordLevel::insert([
            ['name' => 'fonds', 'description' => 'Le fonds est l\'ensemble des documents créés ou reçus par une personne ou un organisme au cours de ses activités.', 'child_id' => 2],
            ['name' => 'sous-fonds', 'description' => 'Le sous-fonds est une division d\'un fonds, correspondant à une subdivision administrative ou fonctionnelle d\'un organisme.', 'child_id' => 3],
            ['name' => 'série', 'description' => 'La série est un regroupement de documents partageant des caractéristiques communes, souvent par fonction ou type de document.', 'child_id' => 4],
            ['name' => 'sous-série', 'description' => 'La sous-série est une subdivision d\'une série, permettant une organisation plus fine des documents.', 'child_id' => 5],
            ['name' => 'dossier', 'description' => 'Le dossier est un ensemble de documents rassemblés parce qu\'ils concernent une même affaire, un même sujet ou une même activité.', 'child_id' => 6],
            ['name' => 'chemise', 'description' => 'La chemise est une unité de rangement à l\'intérieur d\'un dossier, contenant un ou plusieurs documents.', 'child_id' => 7],
            ['name' => 'pièce', 'description' => 'La pièce est le plus petit niveau de description, correspondant à un document individuel.', 'child_id' => null],
        ]);


        RecordSupport::insert([
            ['name' => 'Papier', 'description' => 'Support physique traditionnel pour la documentation'],
            ['name' => 'Microfilm', 'description' => 'Support pour la conservation à long terme des documents'],
            ['name' => 'CD-ROM', 'description' => 'Support numérique pour le stockage de données'],
            ['name' => 'Disque dur externe', 'description' => 'Support portable pour le stockage de données'],
            ['name' => 'Cartouche magnétique', 'description' => 'Support pour les systèmes d\'archivage informatique'],
            ['name' => 'Pellicule photographique', 'description' => 'Support pour la photographie analogique'],
            ['name' => 'Bandes magnétiques', 'description' => 'Support pour l\'archivage de données sur bande'],
            ['name' => 'Parchemin', 'description' => 'Support traditionnel pour l\'écriture manuscrite'],
            ['name' => 'Argile', 'description' => 'Support pour l\'écriture cunéiforme dans l\'Antiquité'],
            ['name' => 'Papyrus', 'description' => 'Support pour l\'écriture dans l\'Égypte antique'],
        ]);


        RecordStatus::insert([
            ['name' => 'Accessionné', 'description' => 'Les archives ont été reçues et enregistrées dans le système.'],
            ['name' => 'En cours de traitement', 'description' => 'Les archives sont en cours d\'inventaire, de classement ou de description.'],
            ['name' => 'Classé', 'description' => 'Les archives ont été classées et sont prêtes à être consultées.'],
            ['name' => 'En cours de restauration', 'description' => 'Les archives sont en cours de restauration ou de réparation.'],
            ['name' => 'Communicable', 'description' => 'Les archives peuvent être consultées par les chercheurs.'],
            ['name' => 'Non communicable', 'description' => 'Les archives sont soumises à des restrictions d\'accès.'],
            ['name' => 'Eliminé', 'description' => 'Les archives ont été détruites conformément aux règles de conservation.'],
            ['name' => 'Transféré', 'description' => 'Les archives ont été transférées à un autre service ou établissement.'],
        ]);

        MailTypology::insert([
            ['name' => 'Demande', 'description' => '', 'class_id' => 1],
            ['name' => 'Lettre', 'description' => '', 'class_id' => 1],
            ['name' => 'Décision', 'description' => '', 'class_id' => 1],
        ]);

        MailStatus::insert([
            ['name' => 'Brouillon'],
            ['name' => 'Traité'],
            ['name' => 'En cours de traitement'],
        ]);

        MailType::insert([
            ['name' => 'send'],
            ['name' => 'received'],
        ]);

        $dg = Organisation::create(['code' => 'DG', 'name' => 'Directeur général', 'description' => '']);
        $sg = Organisation::create(['code' => 'SG', 'name' => 'Secrétaire général', 'description' => '', 'parent_id' => $dg->id]);
        Organisation::create(['code' => 'RC', 'name' => 'Responsable du courier', 'description' => '', 'parent_id' => $sg->id]);
        $sg2 = Organisation::create(['code' => 'SG', 'name' => 'Secrétaire général', 'description' => 'Poste du Secrétaire général']);

        UserOrganisation::insert([
            ['user_id' => 1, 'organisation_id' => $dg->id, 'active' => true],
            ['user_id' => 1, 'organisation_id' => $sg->id, 'active' => false],
            ['user_id' => 1, 'organisation_id' => 3, 'active' => false],
        ]);

        batch::insert([
            ['code' => 'DG10', 'name' => 'Parapheur directeur général'],
            ['code' => 'DG09', 'name' => 'Parapheur Secrétaire général'],
        ]);

        Building::create(['name' => 'Archives de la BCGF', 'description' => '', 'creator_id' =>'1']);
        $floor = Floor::create(['name' => '2e étage', 'description' => '', 'building_id' => 1, 'creator_id' =>1]);

        Room::create(['code' => 'Porte 201', 'name' => 'Archives financières', 'description' => '', 'floor_id' => $floor->id, 'creator_id' =>1]);

        Shelf::create([
            'code' => 'E201-1',
            'observation' => '',
            'face' => 2,
            'ear' => 1,
            'shelf' => 6,
            'shelf_length' => 120,
            'room_id' => 1,
             'creator_id' =>1]);

        DocumentType::create([
            'name' => 'Duplicate',
            'description' => 'A copy of a document that is made for archival purposes.',
        ]);

        DocumentType::create([
            'name' => 'Original',
            'description' => 'The original version of a document.',
        ]);


        DocumentType::create([
            'name' => 'Copy',
            'description' => 'A copy of a document that is made for distribution or reference purposes.',
        ]);

        AuthorType::insert([
            ['name' => 'Personne', 'description' => 'Un individu, identifié par son nom propre.'],
            ['name' => 'Famille', 'description' => 'Un groupe de personnes liées par le sang ou le mariage, identifié par un nom de famille commun.'],
            ['name' => 'Personne morale', 'description' => 'Une entité dotée de la personnalité juridique, distincte des personnes physiques qui la composent (entreprises, associations, fondations, etc.).'],
            ['name' => 'Collectivité territoriale', 'description' => 'Une entité administrative d\'une zone géographique déterminée (commune, département, région, etc.).'],
            ['name' => 'État', 'description' => 'Une entité politique souveraine exerçant le pouvoir sur un territoire et une population.'],
            ['name' => 'Organisation internationale intergouvernementale', 'description' => 'Une organisation composée d\'États membres (ONU, UNESCO, etc.).'],
            ['name' => 'Organisation internationale non gouvernementale', 'description' => 'Une organisation à but non lucratif et non étatique, agissant à l\'échelle internationale (Croix-Rouge, Greenpeace, etc.).']
        ]);


    }
}
