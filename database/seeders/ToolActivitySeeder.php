<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class ToolActivitySeeder extends Seeder
{
    /**
     * The data for the activities.
     *
     * @var array
     */
    protected $activities = [
        [
            'code' => '00',
            'name' => 'DOSSIERS DE RÉFÉRENCE',
            'children' => [],
        ],
        [
            'code' => '01000',
            'name' => 'POLITIQUES ET DIRECTIVES',
            'children' => [
                [
                    'code' => '01000',
                    'name' => 'ORGANISATION ADMINISTRATIVE',
                    'children' => [],
                ],
                [
                    'code' => '01100',
                    'name' => 'GESTION CONSTITUTIVE',
                    'children' => [
                        ['code' => '01130', 'name' => 'HISTORIQUE', 'children' => []],
                    ],
                ],
                [
                    'code' => '01200',
                    'name' => 'GESTION ADMINISTRATIVE',
                    'children' => [
                        [
                            'code' => '01210',
                            'name' => 'PLANIFICATION DU MINISTÈRE',
                            'children' => [
                                ['code' => '01212', 'name' => 'ORIENTATIONS, PRIORITÉS ET OBJECTIFS', 'children' => []],
                                ['code' => '01214', 'name' => 'PLANIFICATION DES ACTIVITÉS', 'children' => []],
                            ],
                        ],
                        [
                            'code' => '01220',
                            'name' => 'STRUCTURE ADMINISTRATIVE',
                            'children' => [
                                ['code' => '01222', 'name' => 'ORGANIGRAMMES', 'children' => []],
                                ['code' => '01224', 'name' => 'PLANS D\'ORGANISATION ADMINISTRATIVE', 'children' => []],
                            ],
                        ],
                        [
                            'code' => '01230',
                            'name' => 'GOUVERNANCE ET ADMINISTRATION',
                            'children' => [
                                ['code' => '01232', 'name' => 'POLITIQUES ET STRATÉGIES DE GESTION', 'children' => []],
                            ],
                        ],
                        [
                            'code' => '01240',
                            'name' => 'COORDINATION ET PARTICIPATION À DES COMITÉS, RÉUNIONS, GROUPES DE TRAVAIL ET TABLES DE CONCERTATION DE GESTION',
                            'children' => [
                                ['code' => '01242', 'name' => 'COORDINATION DES COMITÉS DE GESTION', 'children' => []],
                                ['code' => '01244', 'name' => 'PARTICIPATION À DES COMITÉS DE GESTION', 'children' => []],
                            ],
                        ],
                        ['code' => '01250', 'name' => 'ÉTHIQUE ET DÉONTOLOGIE', 'children' => []],
                        [
                            'code' => '01260',
                            'name' => 'DÉSIGNATION ET DÉLÉGATION',
                            'children' => [
                                ['code' => '01261', 'name' => 'REGISTRE DES DÉSIGNATIONS EN MATIÈRE DE GESTION FINANCIÈRE', 'children' => []],
                                ['code' => '01262', 'name' => 'DÉLÉGATION DE POUVOIRS ET DE SIGNATURE (INTÉRIM)', 'children' => []],
                            ],
                        ],
                        ['code' => '01270', 'name' => 'BRIEFING ET TRAVAUX PARLEMENTAIRES', 'children' => []],
                        [
                            'code' => '01280',
                            'name' => 'DÉCISIONS ET DEMANDES GOUVERNEMENTALES',
                            'children' => [
                                ['code' => '01281', 'name' => 'CT ET DÉCISIONS DU CONSEIL DU TRÉSOR', 'children' => []],
                                ['code' => '01282', 'name' => 'DÉCRETS', 'children' => []],
                                ['code' => '01283', 'name' => 'MÉMOIRES', 'children' => []],
                                ['code' => '01284', 'name' => 'RECOMMANDATIONS DES COMITÉS MINISTÉRIELS', 'children' => []],
                            ],
                        ],
                        ['code' => '01290', 'name' => 'DEMANDES MINISTÉRIELLES', 'children' => []],
                    ],
                ],
                [
                    'code' => '01300',
                    'name' => 'CONTRÔLE ADMINISTRATIF / REDDITION DE COMPTE',
                    'children' => [
                        ['code' => '01310', 'name' => 'RAPPORTS D\'ACTIVITÉS', 'children' => []],
                        ['code' => '01320', 'name' => 'INDICATEURS DE PERFORMANCE', 'children' => []],
                    ],
                ],
                [
                    'code' => '01400',
                    'name' => 'AUDIT, VÉRIFICATION ET ÉVALUATION',
                    'children' => [
                        [
                            'code' => '01410',
                            'name' => 'AUDIT INTERNE (MANDATS)',
                            'children' => [
                                ['code' => '01411', 'name' => 'MANDATS D\'AUDIT', 'children' => []],
                                ['code' => '01412', 'name' => 'MANDATS D\'EXAMEN', 'children' => []],
                                ['code' => '01413', 'name' => 'MANDATS AD HOC', 'children' => []],
                                ['code' => '01414', 'name' => 'MANDATS DE SUIVI DES MANDATS ANTÉRIEURS', 'children' => []],
                                ['code' => '01415', 'name' => 'MANDATS CONSEIL', 'children' => []],
                                ['code' => '01416', 'name' => 'ASSURANCE QUALITÉ DE L\'AUDIT INTERNE', 'children' => []],
                            ],
                        ],
                        [
                            'code' => '01420',
                            'name' => 'VÉRIFICATION EXTERNE',
                            'children' => [
                                ['code' => '01421', 'name' => 'VÉRIFICATEUR GÉNÉRAL', 'children' => []],
                                ['code' => '01422', 'name' => 'CONTRÔLEUR DES FINANCES', 'children' => []],
                                ['code' => '01423', 'name' => 'COMMISSION D\'ACCÈS À L\'INFORMATION', 'children' => []],
                                ['code' => '01424', 'name' => 'COMMISSION DE L\'ADMINISTRATION PUBLIQUE', 'children' => []],
                                ['code' => '01425', 'name' => 'COMMISSION DE LA FONCTION PUBLIQUE', 'children' => []],
                                ['code' => '01426', 'name' => 'PROTECTEUR DU CITOYEN', 'children' => []],
                                ['code' => '01427', 'name' => 'COMMISSAIRE AU DÉVELOPPEMENT DURABLE', 'children' => []],
                            ],
                        ],
                        ['code' => '01450', 'name' => 'GESTION DU RISQUE', 'children' => []],
                        ['code' => '01460', 'name' => 'ÉVALUATION', 'children' => []],
                    ],
                ],
                [
                    'code' => '01500',
                    'name' => 'LÉGISLATION',
                    'children' => [
                        [
                            'code' => '01510',
                            'name' => 'LÉGISLATION QUÉBÉCOISE',
                            'children' => [
                                ['code' => '01512', 'name' => 'LOIS QUÉBÉCOISES', 'children' => []],
                                ['code' => '01514', 'name' => 'RÈGLEMENTS QUÉBÉCOIS', 'children' => []],
                            ],
                        ],
                        ['code' => '01520', 'name' => 'LÉGISLATION CAMEROUNAISE', 'children' => []],
                        ['code' => '01530', 'name' => 'LÉGISLATION INTERNATIONALE', 'children' => []],
                        ['code' => '01540', 'name' => 'PROJET DE LOI', 'children' => []],
                        ['code' => '01550', 'name' => 'PROJET DE RÈGLEMENT', 'children' => []],
                    ],
                ],
                [
                    'code' => '01600',
                    'name' => 'ACTIVITÉS JURIDIQUES',
                    'children' => [
                        ['code' => '01610', 'name' => 'OPINIONS JURIDIQUES', 'children' => []],
                        ['code' => '01620', 'name' => 'POURSUITES JUDICIAIRES', 'children' => []],
                    ],
                ],
                [
                    'code' => '01700',
                    'name' => 'MANDATS D\'ANALYSES D\'AFFAIRES ET DE GESTION DE PROJETS',
                    'children' => [
                        ['code' => '01710', 'name' => 'ARCHITECTURE D\'AFFAIRES', 'children' => []],
                        ['code' => '01720', 'name' => 'ANALYSE D\'AFFAIRES', 'children' => []],
                        ['code' => '01740', 'name' => 'GESTION DE PROJETS', 'children' => []],
                    ],
                ],
            ],
        ],
        // 20 est affecté à normes et procédures
        [
            'code' => '20',
            'name' => 'NORMES ET PROCÉDURES',
            'children' => [],
        ],
        [
            'code' => '04000',
            'name' => 'GESTION DES RESSOURCES FINANCIÈRES',
            'children' => [
                [
                    'code' => '04100',
                    'name' => 'ÉLABORATION DU BUDGET',
                    'children' => [
                        ['code' => '04110', 'name' => 'REVUE DE PROGRAMMES', 'children' => [
                            ['code' => '04111', 'name' => 'BUDGET PROVINCIAL', 'children' => []],
                            ['code' => '04112', 'name' => 'RÉVISION DES ACTIVITÉS BUDGÉTAIRES', 'children' => []],
                            ['code' => '04113', 'name' => 'PROJECTIONS DE LA MASSE SALARIALE', 'children' => []],
                            ['code' => '04114', 'name' => 'PROJECTIONS DU FONCTIONNEMENT', 'children' => []],
                        ]],
                        ['code' => '04120', 'name' => 'CRÉDITS DÉTAILLÉS', 'children' => [
                            ['code' => '04121', 'name' => 'BUDGET DE DÉPENSES', 'children' => []],
                            ['code' => '04122', 'name' => 'BUDGET D\'INVESTISSEMENT', 'children' => []],
                        ]],
                        ['code' => '04130', 'name' => 'PROGRAMMATION BUDGÉTAIRE ET NORMALISATION', 'children' => []],
                        ['code' => '04140', 'name' => 'ÉTUDE DES CRÉDITS', 'children' => []],
                        ['code' => '04150', 'name' => 'PLAN ANNUEL DE GESTION DES DÉPENSES (PAGD)', 'children' => []],
                        ['code' => '04160', 'name' => 'CADRE FINANCIER', 'children' => []],
                        ['code' => '04170', 'name' => 'CONSOLIDATION DES ORGANISMES NON BUDGÉTAIRES ET FONDS SPÉCIAUX (COF)', 'children' => []],
                    ],
                ],
                [
                    'code' => '04200',
                    'name' => 'GESTION DU BUDGET',
                    'children' => [
                        ['code' => '04210', 'name' => 'DISTRIBUTION DÉTAILLÉE DU BUDGET', 'children' => [
                            ['code' => '04212', 'name' => 'STRUCTURE BUDGÉTAIRE', 'children' => []],
                            ['code' => '04213', 'name' => 'SUIVI DES ÉQUIVALENTS TEMPS COMPLET (ETC)', 'children' => []],
                        ]],
                        ['code' => '04220', 'name' => 'CRÉDITS REPORTABLES', 'children' => []],
                        ['code' => '04230', 'name' => 'ENGAGEMENTS FINANCIERS DE 25 000 $ ET PLUS', 'children' => []],
                        ['code' => '04240', 'name' => 'RAPPORT ANNUEL DE GESTION DES DÉPENSES', 'children' => []],
                        ['code' => '04250', 'name' => 'GESTION DES MODIFICATIONS BUDGÉTAIRES', 'children' => [
                            ['code' => '04252', 'name' => 'CRÉDITS ADDITIONNELS OU SUPPLÉMENTAIRES', 'children' => []],
                        ]],
                        ['code' => '04260', 'name' => 'MESURES DE RÉDUCTION DES DÉPENSES OU GEL DES CRÉDITS', 'children' => []],
                        ['code' => '04270', 'name' => 'SUIVI BUDGÉTAIRE - BUDGET DES DÉPENSES', 'children' => []],
                        ['code' => '04280', 'name' => 'SUIVI BUDGÉTAIRE - BUDGET D\'INVESTISSEMENTS', 'children' => []],
                        ['code' => '04290', 'name' => 'PRÉVISIONS DES REVENUS ET RECETTES, DÉPENSES ET DÉBOURSÉS', 'children' => []],
                    ],
                ],
                [
                    'code' => '04300',
                    'name' => 'OPÉRATIONS FINANCIÈRES ET CONTRÔLE DE CONFORMITÉ',
                    'children' => [
                        ['code' => '04340', 'name' => 'GESTION DES REVENUS', 'children' => [
                            ['code' => '04341', 'name' => 'GESTION DE LA FACTURATION ET DE LA TARIFICATION', 'children' => []],
                            ['code' => '04342', 'name' => 'ENCAISSEMENT', 'children' => []],
                            ['code' => '04343', 'name' => 'COMPTES À RECEVOIR', 'children' => []],
                            ['code' => '04344', 'name' => 'AVANCES DE FONDS ET COMPTES EN FIDÉICOMMIS', 'children' => []],
                            ['code' => '04345', 'name' => 'MAUVAISES CRÉANCES', 'children' => []],
                            ['code' => '04346', 'name' => 'FONDS LOCAUX', 'children' => []],
                        ]],
                        ['code' => '04350', 'name' => 'GESTION DES DÉPENSES', 'children' => [
                            ['code' => '04351', 'name' => 'DÉPENSES DE RÉMUNÉRATION', 'children' => []],
                            ['code' => '04352', 'name' => 'IMPÔTS ET TAXES', 'children' => []],
                            ['code' => '04353', 'name' => 'COMPTES À PAYER ESTIMÉS (CAP)', 'children' => []],
                            ['code' => '04354', 'name' => 'FOURNISSEURS', 'children' => []],
                            ['code' => '04357', 'name' => 'FRAIS DE FONCTION', 'children' => []],
                            ['code' => '04358', 'name' => 'GESTION DES DÉBOURS', 'children' => []],
                        ]],
                        ['code' => '04360', 'name' => 'ÉTATS FINANCIERS DU MINISTÈRE', 'children' => [
                            ['code' => '04361', 'name' => 'ÉTATS FINANCIERS MENSUELS', 'children' => []],
                            ['code' => '04362', 'name' => 'ÉTATS FINANCIERS ANNUELS', 'children' => []],
                        ]],
                        ['code' => '04370', 'name' => 'PLAN DE CONTRÔLE ET DE SUPERVISION', 'children' => []],
                        ['code' => '04380', 'name' => 'SUIVI DES PRÊTS, PLACEMENTS ET AVANCES', 'children' => []],
                        ['code' => '04390', 'name' => 'FRAIS DE DÉPLACEMENT', 'children' => []],
                    ],
                ],
                [
                    'code' => '04500',
                    'name' => 'ACTIVITÉS PARTICULIÈRES À LA GESTION DES RESSOURCES FINANCIÈRES',
                    'children' => [
                        ['code' => '04510', 'name' => 'GESTION DES FONDS SPÉCIAUX', 'children' => [
                            ['code' => '04511', 'name' => 'FONDS VERT', 'children' => []],
                            ['code' => '04512', 'name' => 'COMPTES À FINS DÉTERMINÉES', 'children' => []],
                        ]],
                        ['code' => '04520', 'name' => 'SUPPORT-CONSEIL AUX UNITÉS ADMINISTRATIVES', 'children' => []],
                        ['code' => '04530', 'name' => 'CARTES DE CRÉDIT DU MINISTÈRE', 'children' => []],
                    ],
                ],
                [
                    'code' => '04600',
                    'name' => 'GESTION DES SYSTÈMES BUDGÉTAIRES ET COMPTABLES',
                    'children' => [
                        ['code' => '04610', 'name' => 'GESTION DU SYSTÈME SYGBEC', 'children' => [
                            ['code' => '04611', 'name' => 'STRUCTURE D\'INFORMATION SYGBEC', 'children' => []],
                            ['code' => '04612', 'name' => 'SÉCURITÉ SYGBEC', 'children' => []],
                            ['code' => '04613', 'name' => 'LISTES SYGBEC-SAGIP', 'children' => []],
                        ]],
                        ['code' => '04620', 'name' => 'GESTION DU SYSTÈME CIA (CENTRE D\'INFORMATION DE L\'ASSEMBLÉE)', 'children' => [
                            ['code' => '04621', 'name' => 'GESTION DES ACCÈS AU CIA', 'children' => []],
                        ]],
                        ['code' => '04630', 'name' => 'GESTION DU SYSTÈME STEF (SYSTÈME DE TRANSFERT ÉLECTRONIQUE DE FONDS)', 'children' => [
                            ['code' => '04631', 'name' => 'GESTION DES ACCÈS AU STEF', 'children' => []],
                        ]],
                        ['code' => '04640', 'name' => 'GESTION DU SYSTÈME SAGIR (STRATÉGIE D\'AFFAIRES EN GESTION INTÉGRÉE DES RESSOURCES)', 'children' => []],
                    ],
                ],
            ],
        ],
        [
            'code' => '05000',
            'name' => 'MÉMOIRES ET DÉCRETS',
            'children' => [
                [
                    'code' => '05000',
                    'name' => 'GESTION DES RESSOURCES MATÉRIELLES ET IMMOBILIÈRES',
                    'children' => [
                        ['code' => '05100', 'name' => 'ACQUISITION DE BIENS ET SERVICES', 'children' => [
                            ['code' => '05110', 'name' => 'ENTENTES DE SERVICES PARTAGÉS', 'children' => []],
                            ['code' => '05120', 'name' => 'LIBRE-SERVICE ACQUISITION (LSA)', 'children' => []],
                            ['code' => '05140', 'name' => 'GESTION CONTRACTUELLE', 'children' => []],
                        ]],
                        ['code' => '05200', 'name' => 'GESTION DU MOBILIER ET DES BIENS CAPITALISABLES', 'children' => [
                            ['code' => '05210', 'name' => 'INVENTAIRE DU MOBILIER ET DES BIENS CAPITALISABLES', 'children' => [
                                ['code' => '05212', 'name' => 'MOBILIER', 'children' => []],
                                ['code' => '05214', 'name' => 'ÉQUIPEMENTS DE BUREAU', 'children' => []],
                                ['code' => '05216', 'name' => 'ÉQUIPEMENTS ÉLECTRONIQUES ET AUDIOVISUELS', 'children' => []],
                            ]],
                            ['code' => '05220', 'name' => 'DÉVELOPPEMENT INFORMATIQUE (IMMOBILISATION)', 'children' => []],
                            ['code' => '05230', 'name' => 'BÂTIMENTS (IMMOBILISATION)', 'children' => []],
                            ['code' => '05250', 'name' => 'DISPOSITION DU MOBILIER ET DES BIENS CAPITALISABLES ET DÉCLARATION DE SURPLUS', 'children' => []],
                            ['code' => '05260', 'name' => 'FERMETURE MENSUELLE DU MODULE IMMOBILISATIONS (SAGIR)', 'children' => []],
                        ]],
                        ['code' => '05300', 'name' => 'GESTION IMMOBILIÈRE (LOCAUX)', 'children' => [
                            ['code' => '05310', 'name' => 'PLAN TRIENNAL D\'IMMOBILISATION', 'children' => []],
                            ['code' => '05320', 'name' => 'LOCATION DES IMMEUBLES', 'children' => []],
                            ['code' => '05330', 'name' => 'ENTRETIEN, RÉPARATION ET RÉNOVATION DES IMMEUBLES', 'children' => []],
                            ['code' => '05340', 'name' => 'GESTION DES LOCAUX', 'children' => [
                                ['code' => '05341', 'name' => 'GESTION DES ENTREPÔTS', 'children' => []],
                                ['code' => '05342', 'name' => 'RÉPARTITION ET UTILISATION DES ESPACES', 'children' => []],
                                ['code' => '05344', 'name' => 'AMÉNAGEMENT ET RÉAMÉNAGEMENT DES ESPACES', 'children' => []],
                                ['code' => '05346', 'name' => 'ACQUISITION ET RÉTROCESSION DES ESPACES', 'children' => []],
                                ['code' => '05348', 'name' => 'IDENTIFICATION ET SIGNALISATION', 'children' => []],
                                ['code' => '05349', 'name' => 'AFFICHAGE', 'children' => []],
                            ]],
                            ['code' => '05350', 'name' => 'PLAN QUÉBÉCOIS DES INFRASTRUCTURES', 'children' => []],
                            ['code' => '05360', 'name' => 'SÉCURITÉ DES IMMEUBLES', 'children' => [
                                ['code' => '05362', 'name' => 'MESURES DE SÉCURITÉ', 'children' => []],
                                ['code' => '05363', 'name' => 'MESURES D\'URGENCE', 'children' => []],
                                ['code' => '05365', 'name' => 'INCENDIES ET SINISTRES', 'children' => []],
                                ['code' => '05367', 'name' => 'INCIDENTS EN LIEU DE TRAVAIL', 'children' => []],
                            ]],
                            ['code' => '05370', 'name' => 'STATIONNEMENTS', 'children' => []],
                        ]],
                        ['code' => '05500', 'name' => 'GESTION DU COURRIER ET DES MESSAGERIES', 'children' => [
                            ['code' => '05520', 'name' => 'SERVICE DU COURRIER GOUVERNEMENTAL', 'children' => []],
                            ['code' => '05530', 'name' => 'COURRIER INTERNE', 'children' => []],
                        ]],
                        ['code' => '05900', 'name' => 'ENVIRONNEMENT DE TRAVAIL', 'children' => [
                            ['code' => '05910', 'name' => 'ERGONOMIE', 'children' => []],
                            ['code' => '05920', 'name' => 'QUALITÉ DE L\'AIR', 'children' => []],
                            ['code' => '05930', 'name' => 'DÉVELOPPEMENT DURABLE', 'children' => [
                                ['code' => '05931', 'name' => 'RÉCUPÉRATION, RECYCLAGE ET DÉCHIQUETAGE', 'children' => []],
                            ]],
                        ]],
                    ],
                ],
            ],
        ],
        [
            'code' => '06000',
            'name' => 'GESTION DES RESSOURCES INFORMATIONNELLES',
            'children' => [
                ['code' => '06100', 'name' => 'ORIENTATIONS ET PLANIFICATION DES RESSOURCES INFORMATIONNELLES', 'children' => [
                    ['code' => '06120', 'name' => 'BILAN DES RESSOURCES INFORMATIONNELLES', 'children' => []],
                    ['code' => '06140', 'name' => 'CADRE DE GESTION DES RESSOURCES INFORMATIONNELLES', 'children' => []],
                    ['code' => '06160', 'name' => 'PLANIFICATION DES RESSOURCES INFORMATIONNELLES', 'children' => []],
                    ['code' => '06190', 'name' => 'VEILLE EN RESSOURCES INFORMATIONNELLES', 'children' => []],
                ]],
                ['code' => '06200', 'name' => 'GESTION DES INVENTAIRES', 'children' => [
                    ['code' => '06220', 'name' => 'GESTION DES ÉQUIPEMENTS INFORMATIQUES', 'children' => []],
                    ['code' => '06240', 'name' => 'GESTION DES SURPLUS D\'ÉQUIPEMENTS INFORMATIQUES', 'children' => []],
                    ['code' => '06260', 'name' => 'GESTION DES INVENTAIRES DE LOGICIELS ET DE LICENCES', 'children' => []],
                    ['code' => '06280', 'name' => 'GESTION DES INVENTAIRES D\'ÉQUIPEMENTS DES SALLES DE SERVEURS', 'children' => []],
                ]],
                ['code' => '06400', 'name' => 'GESTION DOCUMENTAIRE', 'children' => [
                    ['code' => '06410', 'name' => 'NORMES, PRATIQUES, OUTILS ET ORIENTATIONS DOCUMENTAIRES', 'children' => []],
                    ['code' => '06420', 'name' => 'CRÉATION ET PRÉPARATION DES DOCUMENTS', 'children' => [
                        ['code' => '06422', 'name' => 'GESTION DES FORMULAIRES', 'children' => []],
                        ['code' => '06424', 'name' => 'REPRODUCTION DES DOCUMENTS', 'children' => []],
                    ]],
                    ['code' => '06430', 'name' => 'GESTION DES DOCUMENTS ADMINISTRATIFS', 'children' => [
                        ['code' => '06432', 'name' => 'GESTION DES DOCUMENTS ACTIFS', 'children' => []],
                        ['code' => '06434', 'name' => 'GESTION DES DOCUMENTS SEMI-ACTIFS', 'children' => []],
                        ['code' => '06436', 'name' => 'GESTION DES DOCUMENTS INACTIFS', 'children' => []],
                    ]],
                ]],
                ['code' => '06500', 'name' => 'SÉCURITÉ, ACCÈS ET PROTECTION DE L\'INFORMATION', 'children' => [
                    ['code' => '06510', 'name' => 'INVENTAIRE DE LA SÉCURITÉ DE L\'INFORMATION', 'children' => []],
                    ['code' => '06520', 'name' => 'INCIDENTS RELATIFS À LA SÉCURITÉ DE L\'INFORMATION', 'children' => []],
                    ['code' => '06530', 'name' => 'ANALYSE DE RISQUE DANS LE CADRE DE LA SÉCURITÉ DE L\'INFORMATION', 'children' => []],
                    ['code' => '06540', 'name' => 'ACCESSIBILITÉ ET DROITS AUX DOCUMENTS ADMINISTRATIFS', 'children' => [
                        ['code' => '06541', 'name' => 'PROTECTION DES RENSEIGNEMENTS PERSONNELS', 'children' => []],
                        ['code' => '06542', 'name' => 'GESTION DES DOCUMENTS SENSIBLES', 'children' => []],
                        ['code' => '06543', 'name' => 'CATÉGORISATION DE L\'INFORMATION', 'children' => []],
                        ['code' => '06544', 'name' => 'DEMANDES D\'ACCÈS À L\'INFORMATION', 'children' => []],
                    ]],
                    ['code' => '06550', 'name' => 'PLAN DE PROTECTION DES DOCUMENTS ESSENTIELS', 'children' => []],
                    ['code' => '06560', 'name' => 'PLAN DE RELÈVE ET DE CONTINUITÉ DES SYSTÈMES TECHNOLOGIQUES D\'INFORMATION', 'children' => []],
                    ['code' => '06570', 'name' => 'SÉCURITÉ DE L\'INFORMATION SELON LA DÉNOMINATION DU SYSTÈME', 'children' => []],
                    ['code' => '06580', 'name' => 'SENSIBILISATION', 'children' => []],
                ]],
                ['code' => '06700', 'name' => 'DÉVELOPPEMENT ET ENTRETIEN DES SYSTÈMES', 'children' => [
                    ['code' => '06710', 'name' => 'MÉTHODOLOGIE ET NORMES', 'children' => []],
                    ['code' => '06720', 'name' => 'MODÈLES DE DOCUMENTATION', 'children' => []],
                    ['code' => '06730', 'name' => 'DÉVELOPPEMENT ET ENTRETIEN SELON LA DÉNOMINATION DU SYSTÈME', 'children' => []],
                    ['code' => '06740', 'name' => 'ARCHITECTURE', 'children' => []],
                ]],
                ['code' => '06800', 'name' => 'SERVICES AUX UTILISATEURS DES SYSTÈMES TECHNOLOGIQUES D\'INFORMATION', 'children' => [
                    ['code' => '06810', 'name' => 'CENTRE DE SERVICES', 'children' => [
                        ['code' => '06812', 'name' => 'COMMUNIQUÉS SUR LES INCIDENTS OU LES PROBLÈMES', 'children' => []],
                        ['code' => '06814', 'name' => 'DÉCLARATION DE SERVICES AUX UTILISATEURS', 'children' => []],
                    ]],
                    ['code' => '06820', 'name' => 'GESTION DES NIVEAUX DE SERVICES', 'children' => [
                        ['code' => '06821', 'name' => 'GESTION DES INCIDENTS', 'children' => []],
                        ['code' => '06822', 'name' => 'GESTION DES PROBLÈMES', 'children' => []],
                        ['code' => '06823', 'name' => 'GESTION DES CONFIGURATIONS', 'children' => []],
                        ['code' => '06824', 'name' => 'GESTION DES CHANGEMENTS', 'children' => []],
                        ['code' => '06825', 'name' => 'GESTION DES MISES À JOUR', 'children' => []],
                    ]],
                    ['code' => '06830', 'name' => 'FORMATION DES UTILISATEURS', 'children' => []],
                    ['code' => '06840', 'name' => 'SERVICES AUX UTILISATEURS SELON LA DÉNOMINATION DU SYSTÈME', 'children' => []],
                    ['code' => '06850', 'name' => 'GESTION DES TÉLÉCOMMUNICATIONS', 'children' => [
                        ['code' => '06852', 'name' => 'SYSTÈMES DE TÉLÉPHONIE', 'children' => []],
                        ['code' => '06854', 'name' => 'TÉLÉCOPIEURS', 'children' => []],
                        ['code' => '06856', 'name' => 'TÉLÉCOMMUNICATIONS', 'children' => []],
                        ['code' => '06858', 'name' => 'RELEVÉS DES TÉLÉCOMMUNICATIONS', 'children' => []],
                    ]],
                ]],
                ['code' => '06900', 'name' => 'RÉSEAU ET ENVIRONNEMENT DES SYSTÈMES TECHNOLOGIQUES D\'INFORMATION', 'children' => [
                    ['code' => '06910', 'name' => 'CHOIX TECHNOLOGIQUES', 'children' => []],
                    ['code' => '06920', 'name' => 'INFRASTRUCTURES DES SYSTÈMES TECHNOLOGIQUES', 'children' => [
                        ['code' => '06921', 'name' => 'INFRASTRUCTURE TECHONOLOGIQUE', 'children' => []],
                        ['code' => '06922', 'name' => 'INFRASTRUCTURE INTRANET', 'children' => []],
                        ['code' => '06923', 'name' => 'INFRASTRUCTURE D\'IMPRESSION', 'children' => []],
                        ['code' => '06924', 'name' => 'INFRASTRUCTURE INTERNET', 'children' => []],
                        ['code' => '06926', 'name' => 'INFRASTRUCTURE COURRIEL', 'children' => []],
                        ['code' => '06928', 'name' => 'INFRASTRUCTURE DE COMMUNICATIONS', 'children' => []],
                    ]],
                    ['code' => '06930', 'name' => 'ENVIRONNEMENTS DES SYSTÈMES TECHNOLOGIQUES D\'INFORMATION', 'children' => [
                        ['code' => '06932', 'name' => 'ENVIRONNEMENTS D\'EXPLOITATION', 'children' => []],
                        ['code' => '06934', 'name' => 'ENVIRONNEMENTS DE LABORATOIRE', 'children' => []],
                        ['code' => '06936', 'name' => 'ENVIRONNEMENTS PARTICULIERS', 'children' => []],
                    ]],
                    ['code' => '06940', 'name' => 'ADMINISTRATION DES SERVICES DE RÉPERTOIRE', 'children' => []],
                    ['code' => '06950', 'name' => 'RÉSEAU ET ENVIRONNEMENT SELON LA DÉNOMINATION DU SYSTÈME', 'children' => []],
                ]],
            ],
        ],
        [
            'code' => '07',
            'name' => 'ENTENTES, CONVENTIONS ET PROTOCOLES',
            'children' => [
                ['code' => '07000', 'name' => 'COMMUNICATIONS', 'children' => []],
                ['code' => '07100', 'name' => 'COMMUNICATIONS ET AFFAIRES PUBLIQUES', 'children' => [
                    ['code' => '07110', 'name' => 'ALLOCUTIONS, DISCOURS ET MOTS', 'children' => []],
                    ['code' => '07130', 'name' => 'ORGANISATION D\'ÉVÉNEMENTS', 'children' => []],
                    ['code' => '07150', 'name' => 'COMMUNIQUÉS DE PRESSE', 'children' => []],
                    ['code' => '07160', 'name' => 'CONFÉRENCES DE PRESSE', 'children' => []],
                ]],
                ['code' => '07200', 'name' => 'STRATÉGIES DE COMMUNICATION', 'children' => []],
                ['code' => '07300', 'name' => 'COMMUNICATIONS INTERNES', 'children' => []],
                ['code' => '07400', 'name' => 'PLANIFICATION DES ACTIVITÉS DE COMMUNICATIONS', 'children' => [
                    ['code' => '07410', 'name' => 'PLAN DE COMMUNICATION', 'children' => [
                        ['code' => '07414', 'name' => 'PLAN DE COMMUNICATION ABRÉGÉ', 'children' => []],
                    ]],
                    ['code' => '07420', 'name' => 'PROGRAMMATION DES ACTIVITÉS', 'children' => []],
                    ['code' => '07430', 'name' => 'SECRÉTARIAT À LA COMMUNICATION GOUVERNEMENTALE', 'children' => []],
                ]],
                ['code' => '07500', 'name' => 'PRODUCTIONS ET PUBLICATIONS', 'children' => [
                    ['code' => '07510', 'name' => 'PUBLICATIONS INTERNES ET EXTERNES', 'children' => []],
                    ['code' => '07520', 'name' => 'PRODUCTIONS AUDIOVISUELLES ET MULTIMÉDIAS', 'children' => []],
                    ['code' => '07530', 'name' => 'CONCEPTION GRAPHIQUE ET IMPRESSION', 'children' => []],
                    ['code' => '07540', 'name' => 'RÉVISION LINGUISTIQUE', 'children' => []],
                    ['code' => '07550', 'name' => 'TRADUCTION', 'children' => []],
                    ['code' => '07560', 'name' => 'DROITS D\'AUTEUR', 'children' => []],
                    ['code' => '07570', 'name' => 'DÉPÔT LÉGAL', 'children' => []],
                ]],
                ['code' => '07600', 'name' => 'PROMOTION DES ACTIVITÉS DU MINISTÈRE', 'children' => [
                    ['code' => '07610', 'name' => 'COMMANDITES', 'children' => []],
                    ['code' => '07620', 'name' => 'EXPOSITIONS, KIOSQUES, STANDS ET SALONS', 'children' => []],
                    ['code' => '07630', 'name' => 'OBJETS PROMOTIONNELS', 'children' => []],
                ]],
                ['code' => '07700', 'name' => 'PUBLICITÉS', 'children' => []],
                ['code' => '07800', 'name' => 'RELATIONS AVEC LES MÉDIAS ET ANNONCES PUBLIQUES', 'children' => [
                    ['code' => '07820', 'name' => 'ACCUEIL DES JOURNALISTES', 'children' => []],
                    ['code' => '07840', 'name' => 'DEMANDES DES JOURNALISTES', 'children' => []],
                    ['code' => '07850', 'name' => 'REVUE DE PRESSE', 'children' => []],
                    ['code' => '07860', 'name' => 'LIGNES DE PRESSE', 'children' => []],
                ]],
                ['code' => '07900', 'name' => 'INTRANET ET INTERNET', 'children' => [
                    ['code' => '07930', 'name' => 'PORTAIL GOUVERNEMENTAL DE SERVICES AUX ENTREPRISES (PGSE)', 'children' => []],
                    ['code' => '07940', 'name' => 'GESTION DES SITES INTERNET', 'children' => []],
                    ['code' => '07950', 'name' => 'GESTION DU SITE INTRANET', 'children' => []],
                ]],
            ],
        ],
        ['code' => '08', 'name' => 'ÉVALUATIONS ET VÉRIFICATIONS', 'children' => []],
        ['code' => '09', 'name' => 'PLAINTES', 'children' => []],
        ['code' => '10', 'name' => 'ÉTUDES, RECHERCHES, ANALYSES ET RAPPORTS', 'children' => []],
        [
            'code' => '02',
            'name' => 'RELATIONS EXTÉRIEURES',
            'children' => [
                ['code' => '02000', 'name' => 'RELATIONS AVEC LES DIVERS MINISTÈRES ET ORGANISMES QUÉBÉCOIS DU DOMAINE PUBLIC OU PRIVÉ', 'children' => []],
                ['code' => '02100', 'name' => 'RELATIONS AVEC LES DIVERS ORGANISMES CAMEROUNAIS', 'children' => []],
                [
                    'code' => '02200',
                    'name' => 'RELATIONS FÉDÉRALES-PROVINCIALES',
                    'children' => [
                        ['code' => '02210', 'name' => 'RELATIONS INTERPROVINCIALES', 'children' => []],
                        ['code' => '02220', 'name' => 'RELATIONS MULTILATÉRALES CAMEROUNAISES', 'children' => []],
                    ],
                ],
                [
                    'code' => '02300',
                    'name' => 'RELATIONS INTERNATIONALES',
                    'children' => [
                        [
                            'code' => '02320',
                            'name' => 'RELATIONS INTERNATIONALES MULTILATÉRALES',
                            'children' => [
                                ['code' => '02322', 'name' => 'RELATIONS AVEC LA FRANCOPHONIE', 'children' => []],
                            ],
                        ],
                    ],
                ],
                ['code' => '02400', 'name' => 'RELATIONS AVEC LES ORGANISMES D\'ÉTAT RELEVANT DU MINISTRE', 'children' => []],
                [
                    'code' => '02500',
                    'name' => 'RELATIONS AVEC LES CITOYENS',
                    'children' => [
                        ['code' => '02510', 'name' => 'DÉCLARATION DE SERVICES AUX CITOYENS', 'children' => []],
                        ['code' => '02520', 'name' => 'TOURNÉES MINISTÉRIELLES ET SOUS-MINISTÉRIELLES', 'children' => []],
                        ['code' => '02530', 'name' => 'CIVILITÉS', 'children' => []],
                        ['code' => '02540', 'name' => 'ACCUEIL ET DEMANDES DE RENSEIGNEMENTS', 'children' => []],
                        ['code' => '02550', 'name' => 'PLAINTES', 'children' => []],
                    ],
                ],
            ],
        ],
        [
            'code' => '03',
            'name' => 'COMITÉS, RÉUNIONS, GROUPES DE TRAVAIL ET TABLES DE CONCERTATION',
            'children' => [
                [
                    'code' => '03000',
                    'name' => 'GESTION DES RESSOURCES HUMAINES',
                    'children' => [],
                ],
                [
                    'code' => '03100',
                    'name' => 'GESTION DES EMPLOIS ET DES EFFECTIFS',
                    'children' => [
                        ['code' => '03110', 'name' => 'SUIVI ET CONTRÔLE DE L\'EFFECTIF', 'children' => []],
                        ['code' => '03120', 'name' => 'GESTION PRÉVISIONNELLE DE LA MAIN-D\'OEUVRE (GPMO)', 'children' => []],
                        ['code' => '03130', 'name' => 'POSTES ADDITIONNELS', 'children' => []],
                        ['code' => '03140', 'name' => 'COMPLEXITÉ SUPÉRIEURE DES EMPLOIS PROFESSIONNELS', 'children' => []],
                        ['code' => '03150', 'name' => 'GESTION DE LA CLASSIFICATION DES EMPLOIS', 'children' => []],
                    ],
                ],
                [
                    'code' => '03200',
                    'name' => 'DOTATION ET MOUVEMENT DU PERSONNEL',
                    'children' => [
                        [
                            'code' => '03210',
                            'name' => 'DOTATION DES EMPLOIS RÉGULIERS',
                            'children' => [
                                ['code' => '03212', 'name' => 'AFFECTATION', 'children' => []],
                                ['code' => '03214', 'name' => 'MUTATION', 'children' => []],
                                ['code' => '03215', 'name' => 'PROMOTION', 'children' => []],
                                ['code' => '03216', 'name' => 'PROMOTION SUITE À LA RÉÉVALUATION DE L\'EMPLOI (PRE)', 'children' => []],
                                ['code' => '03217', 'name' => 'PROCESSUS DE RECRUTEMENT ET PROMOTION', 'children' => []],
                                ['code' => '03218', 'name' => 'PROCESSUS DE QUALIFICATION PARTICULIER (PQP)', 'children' => []],
                            ],
                        ],
                        ['code' => '03220', 'name' => 'DOTATION DES EMPLOIS OCCASIONNELS', 'children' => []],
                        ['code' => '03230', 'name' => 'EMBAUCHE D\'ÉTUDIANTS', 'children' => []],
                        ['code' => '03250', 'name' => 'EMBAUCHE DE STAGIAIRES', 'children' => []],
                        ['code' => '03260', 'name' => 'ACCUEIL DES EMPLOYÉS ET INTÉGRATION', 'children' => []],
                        ['code' => '03270', 'name' => 'DESCRIPTIONS D\'EMPLOIS', 'children' => []],
                        ['code' => '03280', 'name' => 'RETRAITE', 'children' => []],
                        ['code' => '03290', 'name' => 'AUTRES MOUVEMENTS DE PERSONNEL', 'children' => []],
                    ],
                ],
                [
                    'code' => '03300',
                    'name' => 'RECONNAISSANCE ET MOBILISATION',
                    'children' => [
                        ['code' => '03310', 'name' => 'PLANIFICATION DES ACTIVITÉS DE RECONNAISSANCE', 'children' => []],
                        ['code' => '03320', 'name' => 'ÉVÉNEMENTS ET REMISE DE PRIX AUX EMPLOYÉS', 'children' => []],
                        ['code' => '03340', 'name' => 'MOBILISATION', 'children' => []],
                    ],
                ],
                [
                    'code' => '03400',
                    'name' => 'DOSSIERS DES EMPLOYÉS',
                    'children' => [
                        ['code' => '03410', 'name' => 'DOSSIERS DES EMPLOYÉS REGULIERS', 'children' => []],
                        ['code' => '03411', 'name' => 'DOSSIERS DES EMPLOYÉS NOMMÉS PAR LE CONSEIL DES MINISTRES', 'children' => []],
                        ['code' => '03420', 'name' => 'DOSSIERS DES EMPLOYÉS OCCASIONNELS', 'children' => []],
                        ['code' => '03430', 'name' => 'DOSSIERS DU PERSONNEL DE CABINET', 'children' => []],
                        ['code' => '03450', 'name' => 'DOSSIERS DES ÉTUDIANTS ET DES STAGIAIRES RÉMUNÉRÉS', 'children' => []],
                        ['code' => '03460', 'name' => 'DOSSIERS D\'INVALIDITÉ', 'children' => []],
                        ['code' => '03470', 'name' => 'ACCIDENTS DE TRAVAIL', 'children' => []],
                    ],
                ],
                [
                    'code' => '03500',
                    'name' => 'RENDEMENT ET PROGRESSION DE CARRIÈRE DES EMPLOYÉS',
                    'children' => [
                        [
                            'code' => '03510',
                            'name' => 'ÉVALUATION DU RENDEMENT',
                            'children' => [
                                ['code' => '03512', 'name' => 'BONI POUR RENDEMENT EXCEPTIONNEL', 'children' => []],
                                ['code' => '03513', 'name' => 'BONIS POUR ÉTUDES DE PERFECTIONNEMENT', 'children' => []],
                            ],
                        ],
                        ['code' => '03520', 'name' => 'PROBATION ET PERMANENCE', 'children' => []],
                        ['code' => '03530', 'name' => 'AVANCEMENT D\'ÉCHELON', 'children' => []],
                        ['code' => '03570', 'name' => 'DÉSIGNATIONS', 'children' => []],
                    ],
                ],
                [
                    'code' => '03600',
                    'name' => 'RÉMUNÉRATION ET AUTRES CONDITIONS DE TRAVAIL',
                    'children' => [
                        [
                            'code' => '03610',
                            'name' => 'ADMINISTRATION DES TRAITEMENTS ET DES BÉNÉFICES',
                            'children' => [
                                ['code' => '03611', 'name' => 'ÉCHELLES DE SALAIRES', 'children' => []],
                                ['code' => '03612', 'name' => 'RÉVISION DES TRAITEMENTS', 'children' => []],
                                ['code' => '03613', 'name' => 'DÉDUCTION À LA SOURCE', 'children' => []],
                            ],
                        ],
                        [
                            'code' => '03630',
                            'name' => 'GESTION DU TEMPS DE TRAVAIL',
                            'children' => [
                                ['code' => '03631', 'name' => 'AMÉNAGEMENT ET RÉDUCTION DU TEMPS DE TRAVAIL', 'children' => []],
                                ['code' => '03632', 'name' => 'ASSIDUITÉ ET ABSENTÉISME', 'children' => []],
                                ['code' => '03633', 'name' => 'ABSENCES POUR ACTIVITÉS SYNDICALES', 'children' => []],
                                ['code' => '03634', 'name' => 'TEMPS PARTIEL ET TEMPS RÉDUIT', 'children' => []],
                                ['code' => '03635', 'name' => 'CONGÉS SANS TRAITEMENT', 'children' => []],
                                ['code' => '03636', 'name' => 'HORAIRES VARIABLES', 'children' => []],
                                ['code' => '03637', 'name' => 'HORAIRES PARTICULIERS', 'children' => []],
                            ],
                        ],
                        ['code' => '03640', 'name' => 'AVANTAGES SOCIAUX', 'children' => []],
                        ['code' => '03650', 'name' => 'TEMPS SUPPLÉMENTAIRE', 'children' => []],
                    ],
                ],
                [
                    'code' => '03700',
                    'name' => 'RELATIONS DE TRAVAIL',
                    'children' => [
                        ['code' => '03710', 'name' => 'RELATIONS PATRONALES-SYNDICALES', 'children' => []],
                        ['code' => '03730', 'name' => 'CONVENTIONS COLLECTIVES', 'children' => []],
                        ['code' => '03740', 'name' => 'CONDITIONS DE TRAVAIL DES EMPLOYÉS NON SYNDIQUÉS ET DES CADRES', 'children' => []],
                        ['code' => '03750', 'name' => 'ARRÊTS DE TRAVAIL', 'children' => []],
                        ['code' => '03760', 'name' => 'PLAINTES, GRIEFS, APPELS ET SENTENCES ARBITRALES', 'children' => []],
                        ['code' => '03770', 'name' => 'CLIENTÈLES ET INTERVENTIONS', 'children' => []],
                    ],
                ],
                [
                    'code' => '03800',
                    'name' => 'SANTÉ ET SÉCURITÉ AU TRAVAIL',
                    'children' => [
                        ['code' => '03810', 'name' => 'PLANIFICATION DES ACTIVITÉS EN SANTÉ DES PERSONNES', 'children' => []],
                        ['code' => '03820', 'name' => 'PROMOTION DE LA SANTÉ', 'children' => []],
                        ['code' => '03830', 'name' => 'SÉCURITÉ AU TRAVAIL', 'children' => []],
                        ['code' => '03840', 'name' => 'PROGRAMME D\'AIDE AU EMPLOYÉS', 'children' => []],
                        ['code' => '03850', 'name' => 'COMMISSION DES NORMES, DE L\'ÉQUITÉ, DE LA SANTÉ ET DE LA SÉCURITÉ AU TRAVAIL', 'children' => []],
                        ['code' => '03860', 'name' => 'RÉSEAUX DE LA SANTÉ ET DE LA SÉCURITÉ DU TRAVAIL', 'children' => []],
                        ['code' => '03870', 'name' => 'PERSONNES HANDICAPÉES', 'children' => []],
                        ['code' => '03880', 'name' => 'INVALIDITÉ ET RÉINTÉGRATION', 'children' => []],
                        ['code' => '03890', 'name' => 'PRÉVENTION DU HARCÈLEMENT', 'children' => []],
                    ],
                ],
                [
                    'code' => '03900',
                    'name' => 'DÉVELOPPEMENT DES PERSONNES ET DE L\'ORGANISATION',
                    'children' => [
                        ['code' => '03910', 'name' => 'PLANIFICATION DES ACTIVITÉS DE DÉVELOPPEMENT', 'children' => []],
                        ['code' => '03920', 'name' => 'FORMATION CORPORATIVE ET PERFECTIONNEMENT', 'children' => []],
                        ['code' => '03930', 'name' => 'TRANSFERT D\'EXPERTISE', 'children' => []],
                        ['code' => '03940', 'name' => 'DÉVELOPPEMENT DES HABILETÉS DE GESTION', 'children' => []],
                        ['code' => '03950', 'name' => 'CLIMAT ORGANISATIONNEL', 'children' => []],
                        ['code' => '03960', 'name' => 'DÉVELOPPEMENT DE LA CARRIÈRE', 'children' => []],
                        ['code' => '03970', 'name' => 'GESTION DU CHANGEMENT', 'children' => []],
                        ['code' => '03980', 'name' => 'INTERVENTIONS EN DÉVELOPPEMENT ORGANISATIONNEL', 'children' => []],
                        ['code' => '03990', 'name' => 'MENTORAT', 'children' => []],
                    ],
                ],
            ],
        ],
        [
            'code' => '40',
            'name' => 'AVIS ET NOTES',
            'children' => [],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Activity::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($this->activities as $activity) {
            // Si le code a 2 chiffres ou moins, c'est un groupe.
            // On crée le groupe, puis ses enfants comme des éléments de haut niveau.
            if (strlen($activity['code']) <= 2) {
                // Crée le groupe lui-même comme un élément de haut niveau
                $this->createActivity([
                    'code' => $activity['code'],
                    'name' => $activity['name'],
                    'children' => []
                ]);

                // Crée ses anciens enfants comme de nouveaux éléments de haut niveau
                if (!empty($activity['children'])) {
                    foreach ($activity['children'] as $child) {
                        $this->createActivity($child); // Ceux-ci peuvent avoir leurs propres enfants
                    }
                }
            } else {
                // Si le code a plus de 2 chiffres (ex: 5), c'est une classe mère.
                // On la traite directement, en conservant sa hiérarchie.
                $this->createActivity($activity);
            }
        }
    }

    private function createActivity(array $activityData, $parentId = null)
    {
        $activity = Activity::create([
            'code' => $activityData['code'],
            'name' => $activityData['name'],
            'parent_id' => $parentId,
        ]);

        if (!empty($activityData['children'])) {
            foreach ($activityData['children'] as $childActivityData) {
                $this->createActivity($childActivityData, $activity->id);
            }
        }
    }
}
