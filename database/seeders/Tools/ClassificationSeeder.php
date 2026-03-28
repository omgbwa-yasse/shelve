<?php

namespace Database\Seeders\Tools;

use App\Models\BookClassification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        Schema::disableForeignKeyConstraints();
        BookClassification::truncate();
        Schema::enableForeignKeyConstraints();

        $dewey = [
            [
                'code' => '000',
                'name' => "Informatique, information et ouvrages gÃ©nÃ©raux",
                'children' => [
                    ['code' => '010', 'name' => "Bibliographie"],
                    ['code' => '020', 'name' => "BibliothÃ©conomie et sciences de l'information"],
                    ['code' => '030', 'name' => "EncyclopÃ©dies gÃ©nÃ©rales"],
                    ['code' => '040', 'name' => "Non attribuÃ©"],
                    ['code' => '050', 'name' => "Publications en sÃ©rie d'ordre gÃ©nÃ©ral"],
                    ['code' => '060', 'name' => "Organisations gÃ©nÃ©rales et musÃ©ologie"],
                    ['code' => '070', 'name' => "MÃ©dias d'information, journalisme, Ã©dition"],
                    ['code' => '080', 'name' => "Recueils gÃ©nÃ©raux"],
                    ['code' => '090', 'name' => "Manuscrits et livres rares"],
                    // Subdivisions for 000
                    ['code' => '001', 'name' => "Connaissance"],
                    ['code' => '002', 'name' => "Le livre"],
                    ['code' => '003', 'name' => "SystÃ¨mes"],
                    ['code' => '004', 'name' => "Informatique et traitement des donnÃ©es"],
                    ['code' => '005', 'name' => "Programmation, programmes, donnÃ©es"],
                    ['code' => '006', 'name' => "MÃ©thodes informatiques spÃ©ciales"],
                ]
            ],
            [
                'code' => '100',
                'name' => "Philosophie et psychologie",
                'children' => [
                    ['code' => '110', 'name' => "MÃ©taphysique"],
                    ['code' => '120', 'name' => "Ã‰pistÃ©mologie, causalitÃ©, genre humain"],
                    ['code' => '130', 'name' => "Parapsychologie et occultisme"],
                    ['code' => '140', 'name' => "Ã‰coles philosophiques spÃ©cifiques"],
                    ['code' => '150', 'name' => "Psychologie"],
                    ['code' => '160', 'name' => "Logique"],
                    ['code' => '170', 'name' => "Ã‰thique (Philosophie morale)"],
                    ['code' => '180', 'name' => "Philosophie antique, mÃ©diÃ©vale, orientale"],
                    ['code' => '190', 'name' => "Philosophie occidentale moderne"],
                    // Subdivisions
                    ['code' => '152', 'name' => "Perception, mouvement, Ã©motions"],
                    ['code' => '153', 'name' => "Processus mentaux et intelligence"],
                    ['code' => '154', 'name' => "Subconscient et Ã©tats modifiÃ©s"],
                    ['code' => '155', 'name' => "Psychologie diffÃ©rentielle et dÃ©veloppementale"],
                    ['code' => '158', 'name' => "Psychologie appliquÃ©e"],
                ]
            ],
            [
                'code' => '200',
                'name' => "Religion",
                'children' => [
                    ['code' => '210', 'name' => "Philosophie et thÃ©orie de la religion"],
                    ['code' => '220', 'name' => "Bible"],
                    ['code' => '230', 'name' => "Christianisme et thÃ©ologie chrÃ©tienne"],
                    ['code' => '240', 'name' => "ThÃ©ologie morale et dÃ©votionnelle chrÃ©tienne"],
                    ['code' => '250', 'name' => "Ordres chrÃ©tiens et Ã©glise locale"],
                    ['code' => '260', 'name' => "ThÃ©ologie sociale et ecclÃ©siologie chrÃ©tienne"],
                    ['code' => '270', 'name' => "Histoire du christianisme"],
                    ['code' => '280', 'name' => "DÃ©nominations et sectes chrÃ©tiennes"],
                    ['code' => '290', 'name' => "Autres religions"],
                    // Subdivisions
                    ['code' => '291', 'name' => "Religion comparÃ©e"],
                    ['code' => '292', 'name' => "Religion classique (grecque et romaine)"],
                    ['code' => '293', 'name' => "Religion germanique"],
                    ['code' => '294', 'name' => "Religions d'origine indienne"],
                    ['code' => '295', 'name' => "Zoroastrisme"],
                    ['code' => '296', 'name' => "JudaÃ¯sme"],
                    ['code' => '297', 'name' => "Islam"],
                    ['code' => '299', 'name' => "Autres religions"],
                ]
            ],
            [
                'code' => '300',
                'name' => "Sciences sociales",
                'children' => [
                    ['code' => '310', 'name' => "Statistiques gÃ©nÃ©rales"],
                    ['code' => '320', 'name' => "Science politique"],
                    ['code' => '330', 'name' => "Ã‰conomie"],
                    ['code' => '340', 'name' => "Droit"],
                    ['code' => '350', 'name' => "Administration publique et science militaire"],
                    ['code' => '360', 'name' => "ProblÃ¨mes et services sociaux"],
                    ['code' => '370', 'name' => "Ã‰ducation"],
                    ['code' => '380', 'name' => "Commerce, communications, transports"],
                    ['code' => '390', 'name' => "Coutumes, Ã©tiquette, folklore"],
                    // Subdivisions
                    ['code' => '301', 'name' => "Sociologie et anthropologie"],
                    ['code' => '302', 'name' => "Interaction sociale"],
                    ['code' => '303', 'name' => "Processus sociaux"],
                    ['code' => '304', 'name' => "Facteurs influenÃ§ant le comportement social"],
                    ['code' => '305', 'name' => "Groupes sociaux"],
                    ['code' => '306', 'name' => "Culture et institutions"],
                    ['code' => '321', 'name' => "SystÃ¨mes de gouvernements et d'Ã©tats"],
                    ['code' => '322', 'name' => "Relations de l'Ã©tat avec des groupes organisÃ©s"],
                    ['code' => '323', 'name' => "Droits civils et politiques"],
                    ['code' => '324', 'name' => "Le processus politique"],
                    ['code' => '325', 'name' => "Migration internationale et colonisation"],
                    ['code' => '326', 'name' => "Esclavage et Ã©mancipation"],
                    ['code' => '327', 'name' => "Relations internationales"],
                    ['code' => '328', 'name' => "Le processus lÃ©gislatif"],
                    ['code' => '331', 'name' => "Ã‰conomie du travail"],
                    ['code' => '332', 'name' => "Ã‰conomie financiÃ¨re"],
                    ['code' => '333', 'name' => "Ã‰conomie de la terre et de l'Ã©nergie"],
                    ['code' => '334', 'name' => "CoopÃ©ratives"],
                    ['code' => '335', 'name' => "Socialisme et systÃ¨mes apparentÃ©s"],
                    ['code' => '336', 'name' => "Finances publiques"],
                    ['code' => '337', 'name' => "Ã‰conomie internationale"],
                    ['code' => '338', 'name' => "Production"],
                    ['code' => '339', 'name' => "MacroÃ©conomie"],
                    ['code' => '341', 'name' => "Droit international"],
                    ['code' => '342', 'name' => "Droit constitutionnel et administratif"],
                    ['code' => '343', 'name' => "Droit militaire, fiscal, commercial, industriel"],
                    ['code' => '344', 'name' => "Droit social, du travail, de la santÃ©, de l'Ã©ducation"],
                    ['code' => '345', 'name' => "Droit pÃ©nal"],
                    ['code' => '346', 'name' => "Droit privÃ©"],
                    ['code' => '347', 'name' => "ProcÃ©dure civile et tribunaux"],
                    ['code' => '348', 'name' => "Lois, rÃ©glementations, jurisprudence"],
                    ['code' => '349', 'name' => "Droit de juridictions spÃ©cifiques"],
                ]
            ],
            [
                'code' => '400',
                'name' => "Langues",
                'children' => [
                    ['code' => '410', 'name' => "Linguistique"],
                    ['code' => '420', 'name' => "Anglais et vieil anglais"],
                    ['code' => '430', 'name' => "Langues germaniques; Allemand"],
                    ['code' => '440', 'name' => "Langues romanes; FranÃ§ais"],
                    ['code' => '450', 'name' => "Italien, roumain, rhÃ©to-roman"],
                    ['code' => '460', 'name' => "Langues espagnole et portugaise"],
                    ['code' => '470', 'name' => "Langues italiques; Latin"],
                    ['code' => '480', 'name' => "Langues hellÃ©niques; Grec classique"],
                    ['code' => '490', 'name' => "Autres langues"],
                    // Subdivisions
                    ['code' => '401', 'name' => "Philosophie et thÃ©orie"],
                    ['code' => '402', 'name' => "Ouvrages divers"],
                    ['code' => '403', 'name' => "Dictionnaires et encyclopÃ©dies"],
                    ['code' => '404', 'name' => "Sujets spÃ©ciaux"],
                    ['code' => '405', 'name' => "Publications en sÃ©rie"],
                    ['code' => '406', 'name' => "Organisations et gestion"],
                    ['code' => '407', 'name' => "Ã‰ducation, recherche, sujets connexes"],
                    ['code' => '408', 'name' => "Traitement parmi des groupes de personnes"],
                    ['code' => '409', 'name' => "Traitement gÃ©ographique et des personnes"],
                ]
            ],
            [
                'code' => '500',
                'name' => "Sciences naturelles et mathÃ©matiques",
                'children' => [
                    ['code' => '510', 'name' => "MathÃ©matiques"],
                    ['code' => '520', 'name' => "Astronomie et sciences connexes"],
                    ['code' => '530', 'name' => "Physique"],
                    ['code' => '540', 'name' => "Chimie et sciences connexes"],
                    ['code' => '550', 'name' => "Sciences de la Terre"],
                    ['code' => '560', 'name' => "PalÃ©ontologie; PalÃ©ozoologie"],
                    ['code' => '570', 'name' => "Sciences de la vie; Biologie"],
                    ['code' => '580', 'name' => "Plantes (Botanique)"],
                    ['code' => '590', 'name' => "Animaux (Zoologie)"],
                    // Subdivisions
                    ['code' => '511', 'name' => "Principes gÃ©nÃ©raux des mathÃ©matiques"],
                    ['code' => '512', 'name' => "AlgÃ¨bre"],
                    ['code' => '513', 'name' => "ArithmÃ©tique"],
                    ['code' => '514', 'name' => "Topologie"],
                    ['code' => '515', 'name' => "Analyse"],
                    ['code' => '516', 'name' => "GÃ©omÃ©trie"],
                    ['code' => '519', 'name' => "ProbabilitÃ©s et mathÃ©matiques appliquÃ©es"],
                    ['code' => '531', 'name' => "MÃ©canique classique"],
                    ['code' => '532', 'name' => "MÃ©canique des fluides"],
                    ['code' => '533', 'name' => "MÃ©canique des gaz"],
                    ['code' => '534', 'name' => "Son et vibrations"],
                    ['code' => '535', 'name' => "LumiÃ¨re et phÃ©nomÃ¨nes paroptiques"],
                    ['code' => '536', 'name' => "Chaleur"],
                    ['code' => '537', 'name' => "Ã‰lectricitÃ© et Ã©lectronique"],
                    ['code' => '538', 'name' => "MagnÃ©tisme"],
                    ['code' => '539', 'name' => "Physique moderne"],
                    ['code' => '571', 'name' => "Physiologie et sujets connexes"],
                    ['code' => '572', 'name' => "Biochimie"],
                    ['code' => '573', 'name' => "SystÃ¨mes physiologiques spÃ©cifiques"],
                    ['code' => '574', 'name' => "(Non utilisÃ©)"],
                    ['code' => '575', 'name' => "Parties spÃ©cifiques et systÃ¨mes"],
                    ['code' => '576', 'name' => "GÃ©nÃ©tique et Ã©volution"],
                    ['code' => '577', 'name' => "Ã‰cologie"],
                    ['code' => '578', 'name' => "Histoire naturelle des organismes"],
                    ['code' => '579', 'name' => "Microorganismes, champignons, algues"],
                ]
            ],
            [
                'code' => '600',
                'name' => "Technologie (Sciences appliquÃ©es)",
                'children' => [
                    ['code' => '610', 'name' => "MÃ©decine et santÃ©"],
                    ['code' => '620', 'name' => "IngÃ©nierie et opÃ©rations connexes"],
                    ['code' => '630', 'name' => "Agriculture et technologies connexes"],
                    ['code' => '640', 'name' => "Gestion du foyer et de la famille"],
                    ['code' => '650', 'name' => "Gestion et services auxiliaires"],
                    ['code' => '660', 'name' => "GÃ©nie chimique"],
                    ['code' => '670', 'name' => "Fabrication industrielle"],
                    ['code' => '680', 'name' => "Fabrication de produits spÃ©cifiques"],
                    ['code' => '690', 'name' => "BÃ¢timent et construction"],
                    // Subdivisions
                    ['code' => '611', 'name' => "Anatomie humaine, cytologie, histologie"],
                    ['code' => '612', 'name' => "Physiologie humaine"],
                    ['code' => '613', 'name' => "Promotion de la santÃ©"],
                    ['code' => '614', 'name' => "Incidence et prÃ©vention des maladies"],
                    ['code' => '615', 'name' => "Pharmacologie et thÃ©rapeutique"],
                    ['code' => '616', 'name' => "Maladies"],
                    ['code' => '617', 'name' => "Chirurgie et spÃ©cialitÃ©s mÃ©dicales"],
                    ['code' => '618', 'name' => "GynÃ©cologie et autres spÃ©cialitÃ©s"],
                    ['code' => '619', 'name' => "MÃ©decine expÃ©rimentale"],
                    ['code' => '621', 'name' => "Physique appliquÃ©e"],
                    ['code' => '622', 'name' => "GÃ©nie minier et opÃ©rations connexes"],
                    ['code' => '623', 'name' => "GÃ©nie militaire et naval"],
                    ['code' => '624', 'name' => "GÃ©nie civil"],
                    ['code' => '625', 'name' => "GÃ©nie des chemins de fer et des routes"],
                    ['code' => '627', 'name' => "GÃ©nie hydraulique"],
                    ['code' => '628', 'name' => "GÃ©nie sanitaire et municipal"],
                    ['code' => '629', 'name' => "Autres branches de l'ingÃ©nierie"],
                    ['code' => '651', 'name' => "Services de bureau"],
                    ['code' => '652', 'name' => "ProcÃ©dÃ©s de communication Ã©crite"],
                    ['code' => '653', 'name' => "StÃ©nographie"],
                    ['code' => '657', 'name' => "ComptabilitÃ©"],
                    ['code' => '658', 'name' => "Gestion gÃ©nÃ©rale"],
                    ['code' => '659', 'name' => "PublicitÃ© et relations publiques"],
                ]
            ],
            [
                'code' => '700',
                'name' => "Arts et loisirs",
                'children' => [
                    ['code' => '710', 'name' => "Urbanisme et art du paysage"],
                    ['code' => '720', 'name' => "Architecture"],
                    ['code' => '730', 'name' => "Arts plastiques; Sculpture"],
                    ['code' => '740', 'name' => "Dessin et arts dÃ©coratifs"],
                    ['code' => '750', 'name' => "Peinture et peintures"],
                    ['code' => '760', 'name' => "Arts graphiques; Gravure et estampes"],
                    ['code' => '770', 'name' => "Photographie et photographies"],
                    ['code' => '780', 'name' => "Musique"],
                    ['code' => '790', 'name' => "Arts rÃ©crÃ©atifs et du spectacle"],
                    // Subdivisions
                    ['code' => '791', 'name' => "ReprÃ©sentations scÃ©niques"],
                    ['code' => '792', 'name' => "ThÃ©Ã¢tre (ReprÃ©sentations sur scÃ¨ne)"],
                    ['code' => '793', 'name' => "Jeux et amusements d'intÃ©rieur"],
                    ['code' => '794', 'name' => "Jeux d'habiletÃ© d'intÃ©rieur"],
                    ['code' => '795', 'name' => "Jeux de hasard"],
                    ['code' => '796', 'name' => "Sports et jeux athlÃ©tiques et d'extÃ©rieur"],
                    ['code' => '797', 'name' => "Sports nautiques et aÃ©riens"],
                    ['code' => '798', 'name' => "Sports Ã©questres et courses d'animaux"],
                    ['code' => '799', 'name' => "PÃªche, chasse, tir"],
                ]
            ],
            [
                'code' => '800',
                'name' => "LittÃ©rature",
                'children' => [
                    ['code' => '810', 'name' => "LittÃ©rature amÃ©ricaine en anglais"],
                    ['code' => '820', 'name' => "LittÃ©rature anglaise et vieil anglais"],
                    ['code' => '830', 'name' => "LittÃ©ratures des langues germaniques"],
                    ['code' => '840', 'name' => "LittÃ©ratures des langues romanes"],
                    ['code' => '850', 'name' => "LittÃ©ratures italienne, roumaine"],
                    ['code' => '860', 'name' => "LittÃ©ratures espagnole et portugaise"],
                    ['code' => '870', 'name' => "LittÃ©ratures des langues italiques; Latine"],
                    ['code' => '880', 'name' => "LittÃ©ratures des langues hellÃ©niques; Grecque"],
                    ['code' => '890', 'name' => "LittÃ©ratures des autres langues"],
                    // Subdivisions
                    ['code' => '801', 'name' => "Philosophie et thÃ©orie"],
                    ['code' => '802', 'name' => "Ouvrages divers"],
                    ['code' => '803', 'name' => "Dictionnaires et encyclopÃ©dies"],
                    ['code' => '805', 'name' => "Publications en sÃ©rie"],
                    ['code' => '806', 'name' => "Organisations"],
                    ['code' => '807', 'name' => "Ã‰ducation, recherche, sujets connexes"],
                    ['code' => '808', 'name' => "RhÃ©torique et recueils"],
                    ['code' => '809', 'name' => "Histoire, description, critique"],
                    ['code' => '811', 'name' => "PoÃ©sie amÃ©ricaine"],
                    ['code' => '812', 'name' => "ThÃ©Ã¢tre amÃ©ricain"],
                    ['code' => '813', 'name' => "Fiction amÃ©ricaine"],
                    ['code' => '814', 'name' => "Essais amÃ©ricains"],
                    ['code' => '815', 'name' => "Discours amÃ©ricains"],
                    ['code' => '816', 'name' => "Lettres amÃ©ricaines"],
                    ['code' => '817', 'name' => "Satire et humour amÃ©ricains"],
                    ['code' => '818', 'name' => "Ã‰crits divers amÃ©ricains"],
                    ['code' => '841', 'name' => "PoÃ©sie franÃ§aise"],
                    ['code' => '842', 'name' => "ThÃ©Ã¢tre franÃ§ais"],
                    ['code' => '843', 'name' => "Roman franÃ§ais"],
                    ['code' => '844', 'name' => "Essais franÃ§ais"],
                    ['code' => '845', 'name' => "Discours franÃ§ais"],
                    ['code' => '846', 'name' => "Lettres franÃ§aises"],
                    ['code' => '847', 'name' => "Satire et humour franÃ§ais"],
                    ['code' => '848', 'name' => "Ã‰crits divers franÃ§ais"],
                ]
            ],
            [
                'code' => '900',
                'name' => "Histoire et gÃ©ographie",
                'children' => [
                    ['code' => '910', 'name' => "GÃ©ographie et voyages"],
                    ['code' => '920', 'name' => "Biographie, gÃ©nÃ©alogie, insignes"],
                    ['code' => '930', 'name' => "Histoire du monde antique"],
                    ['code' => '940', 'name' => "Histoire de l'Europe"],
                    ['code' => '950', 'name' => "Histoire de l'Asie; Orient; ExtrÃªme-Orient"],
                    ['code' => '960', 'name' => "Histoire de l'Afrique"],
                    ['code' => '970', 'name' => "Histoire de l'AmÃ©rique du Nord"],
                    ['code' => '980', 'name' => "Histoire de l'AmÃ©rique du Sud"],
                    ['code' => '990', 'name' => "Histoire des autres parties du monde"],
                    // Subdivisions
                    ['code' => '901', 'name' => "Philosophie et thÃ©orie de l'histoire"],
                    ['code' => '902', 'name' => "Ouvrages divers"],
                    ['code' => '903', 'name' => "Dictionnaires et encyclopÃ©dies"],
                    ['code' => '904', 'name' => "RÃ©cits d'Ã©vÃ©nements"],
                    ['code' => '905', 'name' => "Publications en sÃ©rie"],
                    ['code' => '906', 'name' => "Organisations et gestion"],
                    ['code' => '907', 'name' => "Ã‰ducation, recherche, sujets connexes"],
                    ['code' => '908', 'name' => "Traitement parmi des groupes de personnes"],
                    ['code' => '909', 'name' => "Histoire du monde"],
                    ['code' => '941', 'name' => "ÃŽles britanniques"],
                    ['code' => '942', 'name' => "Angleterre et Pays de Galles"],
                    ['code' => '943', 'name' => "Europe centrale; Allemagne"],
                    ['code' => '944', 'name' => "France et Monaco"],
                    ['code' => '945', 'name' => "PÃ©ninsule italienne et Ã®les adjacentes"],
                    ['code' => '946', 'name' => "PÃ©ninsule ibÃ©rique et Ã®les adjacentes"],
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

