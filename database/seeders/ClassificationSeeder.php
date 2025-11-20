<?php

namespace Database\Seeders;

use App\Models\BookClassification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BookClassification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $dewey = [
            [
                'code' => '000',
                'name' => "Informatique, information et ouvrages généraux",
                'children' => [
                    ['code' => '010', 'name' => "Bibliographie"],
                    ['code' => '020', 'name' => "Bibliothéconomie et sciences de l'information"],
                    ['code' => '030', 'name' => "Encyclopédies générales"],
                    ['code' => '040', 'name' => "Non attribué"],
                    ['code' => '050', 'name' => "Publications en série d'ordre général"],
                    ['code' => '060', 'name' => "Organisations générales et muséologie"],
                    ['code' => '070', 'name' => "Médias d'information, journalisme, édition"],
                    ['code' => '080', 'name' => "Recueils généraux"],
                    ['code' => '090', 'name' => "Manuscrits et livres rares"],
                    // Subdivisions for 000
                    ['code' => '001', 'name' => "Connaissance"],
                    ['code' => '002', 'name' => "Le livre"],
                    ['code' => '003', 'name' => "Systèmes"],
                    ['code' => '004', 'name' => "Informatique et traitement des données"],
                    ['code' => '005', 'name' => "Programmation, programmes, données"],
                    ['code' => '006', 'name' => "Méthodes informatiques spéciales"],
                ]
            ],
            [
                'code' => '100',
                'name' => "Philosophie et psychologie",
                'children' => [
                    ['code' => '110', 'name' => "Métaphysique"],
                    ['code' => '120', 'name' => "Épistémologie, causalité, genre humain"],
                    ['code' => '130', 'name' => "Parapsychologie et occultisme"],
                    ['code' => '140', 'name' => "Écoles philosophiques spécifiques"],
                    ['code' => '150', 'name' => "Psychologie"],
                    ['code' => '160', 'name' => "Logique"],
                    ['code' => '170', 'name' => "Éthique (Philosophie morale)"],
                    ['code' => '180', 'name' => "Philosophie antique, médiévale, orientale"],
                    ['code' => '190', 'name' => "Philosophie occidentale moderne"],
                    // Subdivisions
                    ['code' => '152', 'name' => "Perception, mouvement, émotions"],
                    ['code' => '153', 'name' => "Processus mentaux et intelligence"],
                    ['code' => '154', 'name' => "Subconscient et états modifiés"],
                    ['code' => '155', 'name' => "Psychologie différentielle et développementale"],
                    ['code' => '158', 'name' => "Psychologie appliquée"],
                ]
            ],
            [
                'code' => '200',
                'name' => "Religion",
                'children' => [
                    ['code' => '210', 'name' => "Philosophie et théorie de la religion"],
                    ['code' => '220', 'name' => "Bible"],
                    ['code' => '230', 'name' => "Christianisme et théologie chrétienne"],
                    ['code' => '240', 'name' => "Théologie morale et dévotionnelle chrétienne"],
                    ['code' => '250', 'name' => "Ordres chrétiens et église locale"],
                    ['code' => '260', 'name' => "Théologie sociale et ecclésiologie chrétienne"],
                    ['code' => '270', 'name' => "Histoire du christianisme"],
                    ['code' => '280', 'name' => "Dénominations et sectes chrétiennes"],
                    ['code' => '290', 'name' => "Autres religions"],
                    // Subdivisions
                    ['code' => '291', 'name' => "Religion comparée"],
                    ['code' => '292', 'name' => "Religion classique (grecque et romaine)"],
                    ['code' => '293', 'name' => "Religion germanique"],
                    ['code' => '294', 'name' => "Religions d'origine indienne"],
                    ['code' => '295', 'name' => "Zoroastrisme"],
                    ['code' => '296', 'name' => "Judaïsme"],
                    ['code' => '297', 'name' => "Islam"],
                    ['code' => '299', 'name' => "Autres religions"],
                ]
            ],
            [
                'code' => '300',
                'name' => "Sciences sociales",
                'children' => [
                    ['code' => '310', 'name' => "Statistiques générales"],
                    ['code' => '320', 'name' => "Science politique"],
                    ['code' => '330', 'name' => "Économie"],
                    ['code' => '340', 'name' => "Droit"],
                    ['code' => '350', 'name' => "Administration publique et science militaire"],
                    ['code' => '360', 'name' => "Problèmes et services sociaux"],
                    ['code' => '370', 'name' => "Éducation"],
                    ['code' => '380', 'name' => "Commerce, communications, transports"],
                    ['code' => '390', 'name' => "Coutumes, étiquette, folklore"],
                    // Subdivisions
                    ['code' => '301', 'name' => "Sociologie et anthropologie"],
                    ['code' => '302', 'name' => "Interaction sociale"],
                    ['code' => '303', 'name' => "Processus sociaux"],
                    ['code' => '304', 'name' => "Facteurs influençant le comportement social"],
                    ['code' => '305', 'name' => "Groupes sociaux"],
                    ['code' => '306', 'name' => "Culture et institutions"],
                    ['code' => '321', 'name' => "Systèmes de gouvernements et d'états"],
                    ['code' => '322', 'name' => "Relations de l'état avec des groupes organisés"],
                    ['code' => '323', 'name' => "Droits civils et politiques"],
                    ['code' => '324', 'name' => "Le processus politique"],
                    ['code' => '325', 'name' => "Migration internationale et colonisation"],
                    ['code' => '326', 'name' => "Esclavage et émancipation"],
                    ['code' => '327', 'name' => "Relations internationales"],
                    ['code' => '328', 'name' => "Le processus législatif"],
                    ['code' => '331', 'name' => "Économie du travail"],
                    ['code' => '332', 'name' => "Économie financière"],
                    ['code' => '333', 'name' => "Économie de la terre et de l'énergie"],
                    ['code' => '334', 'name' => "Coopératives"],
                    ['code' => '335', 'name' => "Socialisme et systèmes apparentés"],
                    ['code' => '336', 'name' => "Finances publiques"],
                    ['code' => '337', 'name' => "Économie internationale"],
                    ['code' => '338', 'name' => "Production"],
                    ['code' => '339', 'name' => "Macroéconomie"],
                    ['code' => '341', 'name' => "Droit international"],
                    ['code' => '342', 'name' => "Droit constitutionnel et administratif"],
                    ['code' => '343', 'name' => "Droit militaire, fiscal, commercial, industriel"],
                    ['code' => '344', 'name' => "Droit social, du travail, de la santé, de l'éducation"],
                    ['code' => '345', 'name' => "Droit pénal"],
                    ['code' => '346', 'name' => "Droit privé"],
                    ['code' => '347', 'name' => "Procédure civile et tribunaux"],
                    ['code' => '348', 'name' => "Lois, réglementations, jurisprudence"],
                    ['code' => '349', 'name' => "Droit de juridictions spécifiques"],
                ]
            ],
            [
                'code' => '400',
                'name' => "Langues",
                'children' => [
                    ['code' => '410', 'name' => "Linguistique"],
                    ['code' => '420', 'name' => "Anglais et vieil anglais"],
                    ['code' => '430', 'name' => "Langues germaniques; Allemand"],
                    ['code' => '440', 'name' => "Langues romanes; Français"],
                    ['code' => '450', 'name' => "Italien, roumain, rhéto-roman"],
                    ['code' => '460', 'name' => "Langues espagnole et portugaise"],
                    ['code' => '470', 'name' => "Langues italiques; Latin"],
                    ['code' => '480', 'name' => "Langues helléniques; Grec classique"],
                    ['code' => '490', 'name' => "Autres langues"],
                    // Subdivisions
                    ['code' => '401', 'name' => "Philosophie et théorie"],
                    ['code' => '402', 'name' => "Ouvrages divers"],
                    ['code' => '403', 'name' => "Dictionnaires et encyclopédies"],
                    ['code' => '404', 'name' => "Sujets spéciaux"],
                    ['code' => '405', 'name' => "Publications en série"],
                    ['code' => '406', 'name' => "Organisations et gestion"],
                    ['code' => '407', 'name' => "Éducation, recherche, sujets connexes"],
                    ['code' => '408', 'name' => "Traitement parmi des groupes de personnes"],
                    ['code' => '409', 'name' => "Traitement géographique et des personnes"],
                ]
            ],
            [
                'code' => '500',
                'name' => "Sciences naturelles et mathématiques",
                'children' => [
                    ['code' => '510', 'name' => "Mathématiques"],
                    ['code' => '520', 'name' => "Astronomie et sciences connexes"],
                    ['code' => '530', 'name' => "Physique"],
                    ['code' => '540', 'name' => "Chimie et sciences connexes"],
                    ['code' => '550', 'name' => "Sciences de la Terre"],
                    ['code' => '560', 'name' => "Paléontologie; Paléozoologie"],
                    ['code' => '570', 'name' => "Sciences de la vie; Biologie"],
                    ['code' => '580', 'name' => "Plantes (Botanique)"],
                    ['code' => '590', 'name' => "Animaux (Zoologie)"],
                    // Subdivisions
                    ['code' => '511', 'name' => "Principes généraux des mathématiques"],
                    ['code' => '512', 'name' => "Algèbre"],
                    ['code' => '513', 'name' => "Arithmétique"],
                    ['code' => '514', 'name' => "Topologie"],
                    ['code' => '515', 'name' => "Analyse"],
                    ['code' => '516', 'name' => "Géométrie"],
                    ['code' => '519', 'name' => "Probabilités et mathématiques appliquées"],
                    ['code' => '531', 'name' => "Mécanique classique"],
                    ['code' => '532', 'name' => "Mécanique des fluides"],
                    ['code' => '533', 'name' => "Mécanique des gaz"],
                    ['code' => '534', 'name' => "Son et vibrations"],
                    ['code' => '535', 'name' => "Lumière et phénomènes paroptiques"],
                    ['code' => '536', 'name' => "Chaleur"],
                    ['code' => '537', 'name' => "Électricité et électronique"],
                    ['code' => '538', 'name' => "Magnétisme"],
                    ['code' => '539', 'name' => "Physique moderne"],
                    ['code' => '571', 'name' => "Physiologie et sujets connexes"],
                    ['code' => '572', 'name' => "Biochimie"],
                    ['code' => '573', 'name' => "Systèmes physiologiques spécifiques"],
                    ['code' => '574', 'name' => "(Non utilisé)"],
                    ['code' => '575', 'name' => "Parties spécifiques et systèmes"],
                    ['code' => '576', 'name' => "Génétique et évolution"],
                    ['code' => '577', 'name' => "Écologie"],
                    ['code' => '578', 'name' => "Histoire naturelle des organismes"],
                    ['code' => '579', 'name' => "Microorganismes, champignons, algues"],
                ]
            ],
            [
                'code' => '600',
                'name' => "Technologie (Sciences appliquées)",
                'children' => [
                    ['code' => '610', 'name' => "Médecine et santé"],
                    ['code' => '620', 'name' => "Ingénierie et opérations connexes"],
                    ['code' => '630', 'name' => "Agriculture et technologies connexes"],
                    ['code' => '640', 'name' => "Gestion du foyer et de la famille"],
                    ['code' => '650', 'name' => "Gestion et services auxiliaires"],
                    ['code' => '660', 'name' => "Génie chimique"],
                    ['code' => '670', 'name' => "Fabrication industrielle"],
                    ['code' => '680', 'name' => "Fabrication de produits spécifiques"],
                    ['code' => '690', 'name' => "Bâtiment et construction"],
                    // Subdivisions
                    ['code' => '611', 'name' => "Anatomie humaine, cytologie, histologie"],
                    ['code' => '612', 'name' => "Physiologie humaine"],
                    ['code' => '613', 'name' => "Promotion de la santé"],
                    ['code' => '614', 'name' => "Incidence et prévention des maladies"],
                    ['code' => '615', 'name' => "Pharmacologie et thérapeutique"],
                    ['code' => '616', 'name' => "Maladies"],
                    ['code' => '617', 'name' => "Chirurgie et spécialités médicales"],
                    ['code' => '618', 'name' => "Gynécologie et autres spécialités"],
                    ['code' => '619', 'name' => "Médecine expérimentale"],
                    ['code' => '621', 'name' => "Physique appliquée"],
                    ['code' => '622', 'name' => "Génie minier et opérations connexes"],
                    ['code' => '623', 'name' => "Génie militaire et naval"],
                    ['code' => '624', 'name' => "Génie civil"],
                    ['code' => '625', 'name' => "Génie des chemins de fer et des routes"],
                    ['code' => '627', 'name' => "Génie hydraulique"],
                    ['code' => '628', 'name' => "Génie sanitaire et municipal"],
                    ['code' => '629', 'name' => "Autres branches de l'ingénierie"],
                    ['code' => '651', 'name' => "Services de bureau"],
                    ['code' => '652', 'name' => "Procédés de communication écrite"],
                    ['code' => '653', 'name' => "Sténographie"],
                    ['code' => '657', 'name' => "Comptabilité"],
                    ['code' => '658', 'name' => "Gestion générale"],
                    ['code' => '659', 'name' => "Publicité et relations publiques"],
                ]
            ],
            [
                'code' => '700',
                'name' => "Arts et loisirs",
                'children' => [
                    ['code' => '710', 'name' => "Urbanisme et art du paysage"],
                    ['code' => '720', 'name' => "Architecture"],
                    ['code' => '730', 'name' => "Arts plastiques; Sculpture"],
                    ['code' => '740', 'name' => "Dessin et arts décoratifs"],
                    ['code' => '750', 'name' => "Peinture et peintures"],
                    ['code' => '760', 'name' => "Arts graphiques; Gravure et estampes"],
                    ['code' => '770', 'name' => "Photographie et photographies"],
                    ['code' => '780', 'name' => "Musique"],
                    ['code' => '790', 'name' => "Arts récréatifs et du spectacle"],
                    // Subdivisions
                    ['code' => '791', 'name' => "Représentations scéniques"],
                    ['code' => '792', 'name' => "Théâtre (Représentations sur scène)"],
                    ['code' => '793', 'name' => "Jeux et amusements d'intérieur"],
                    ['code' => '794', 'name' => "Jeux d'habileté d'intérieur"],
                    ['code' => '795', 'name' => "Jeux de hasard"],
                    ['code' => '796', 'name' => "Sports et jeux athlétiques et d'extérieur"],
                    ['code' => '797', 'name' => "Sports nautiques et aériens"],
                    ['code' => '798', 'name' => "Sports équestres et courses d'animaux"],
                    ['code' => '799', 'name' => "Pêche, chasse, tir"],
                ]
            ],
            [
                'code' => '800',
                'name' => "Littérature",
                'children' => [
                    ['code' => '810', 'name' => "Littérature américaine en anglais"],
                    ['code' => '820', 'name' => "Littérature anglaise et vieil anglais"],
                    ['code' => '830', 'name' => "Littératures des langues germaniques"],
                    ['code' => '840', 'name' => "Littératures des langues romanes"],
                    ['code' => '850', 'name' => "Littératures italienne, roumaine"],
                    ['code' => '860', 'name' => "Littératures espagnole et portugaise"],
                    ['code' => '870', 'name' => "Littératures des langues italiques; Latine"],
                    ['code' => '880', 'name' => "Littératures des langues helléniques; Grecque"],
                    ['code' => '890', 'name' => "Littératures des autres langues"],
                    // Subdivisions
                    ['code' => '801', 'name' => "Philosophie et théorie"],
                    ['code' => '802', 'name' => "Ouvrages divers"],
                    ['code' => '803', 'name' => "Dictionnaires et encyclopédies"],
                    ['code' => '805', 'name' => "Publications en série"],
                    ['code' => '806', 'name' => "Organisations"],
                    ['code' => '807', 'name' => "Éducation, recherche, sujets connexes"],
                    ['code' => '808', 'name' => "Rhétorique et recueils"],
                    ['code' => '809', 'name' => "Histoire, description, critique"],
                    ['code' => '811', 'name' => "Poésie américaine"],
                    ['code' => '812', 'name' => "Théâtre américain"],
                    ['code' => '813', 'name' => "Fiction américaine"],
                    ['code' => '814', 'name' => "Essais américains"],
                    ['code' => '815', 'name' => "Discours américains"],
                    ['code' => '816', 'name' => "Lettres américaines"],
                    ['code' => '817', 'name' => "Satire et humour américains"],
                    ['code' => '818', 'name' => "Écrits divers américains"],
                    ['code' => '841', 'name' => "Poésie française"],
                    ['code' => '842', 'name' => "Théâtre français"],
                    ['code' => '843', 'name' => "Roman français"],
                    ['code' => '844', 'name' => "Essais français"],
                    ['code' => '845', 'name' => "Discours français"],
                    ['code' => '846', 'name' => "Lettres françaises"],
                    ['code' => '847', 'name' => "Satire et humour français"],
                    ['code' => '848', 'name' => "Écrits divers français"],
                ]
            ],
            [
                'code' => '900',
                'name' => "Histoire et géographie",
                'children' => [
                    ['code' => '910', 'name' => "Géographie et voyages"],
                    ['code' => '920', 'name' => "Biographie, généalogie, insignes"],
                    ['code' => '930', 'name' => "Histoire du monde antique"],
                    ['code' => '940', 'name' => "Histoire de l'Europe"],
                    ['code' => '950', 'name' => "Histoire de l'Asie; Orient; Extrême-Orient"],
                    ['code' => '960', 'name' => "Histoire de l'Afrique"],
                    ['code' => '970', 'name' => "Histoire de l'Amérique du Nord"],
                    ['code' => '980', 'name' => "Histoire de l'Amérique du Sud"],
                    ['code' => '990', 'name' => "Histoire des autres parties du monde"],
                    // Subdivisions
                    ['code' => '901', 'name' => "Philosophie et théorie de l'histoire"],
                    ['code' => '902', 'name' => "Ouvrages divers"],
                    ['code' => '903', 'name' => "Dictionnaires et encyclopédies"],
                    ['code' => '904', 'name' => "Récits d'événements"],
                    ['code' => '905', 'name' => "Publications en série"],
                    ['code' => '906', 'name' => "Organisations et gestion"],
                    ['code' => '907', 'name' => "Éducation, recherche, sujets connexes"],
                    ['code' => '908', 'name' => "Traitement parmi des groupes de personnes"],
                    ['code' => '909', 'name' => "Histoire du monde"],
                    ['code' => '941', 'name' => "Îles britanniques"],
                    ['code' => '942', 'name' => "Angleterre et Pays de Galles"],
                    ['code' => '943', 'name' => "Europe centrale; Allemagne"],
                    ['code' => '944', 'name' => "France et Monaco"],
                    ['code' => '945', 'name' => "Péninsule italienne et îles adjacentes"],
                    ['code' => '946', 'name' => "Péninsule ibérique et îles adjacentes"],
                    ['code' => '947', 'name' => "Europe de l'Est; Russie"],
                    ['code' => '948', 'name' => "Scandinavie"],
                    ['code' => '949', 'name' => "Autres parties de l'Europe"],
                ]
            ],
        ];

        $this->insertClassifications($dewey);
    }

    private function insertClassifications(array $classifications, ?int $parentId = null)
    {
        foreach ($classifications as $data) {
            $classification = BookClassification::create([
                'name' => $data['code'] . ' - ' . $data['name'],
                'description' => $data['name'],
                'parent_id' => $parentId,
            ]);

            if (isset($data['children'])) {
                $this->insertClassifications($data['children'], $classification->id);
            }
        }
    }
}
