<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ThesaurusGeographiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer le schéma de thésaurus géographique (idempotent)
        $scheme = DB::table('thesaurus_schemes')
            ->where('uri', 'https://geo.cameroun.cm/thesaurus/geographique')
            ->first();
        if ($scheme) {
            $schemeId = $scheme->id;
        } else {
            $schemeId = DB::table('thesaurus_schemes')->insertGetId([
                'uri' => 'https://geo.cameroun.cm/thesaurus/geographique',
                'identifier' => 'GEO-CMR-2025',
                'title' => 'Thésaurus Géographique du Cameroun',
                'description' => 'Classification hiérarchique des entités géographiques du Cameroun, depuis le niveau mondial jusqu\'aux villes et localités',
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Structure hiérarchique des concepts géographiques
        $geoHierarchy = [
            // Niveau 1 : Monde
            'MONDE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/monde',
                'notation' => 'GEO.01',
                'prefLabel' => 'Monde',
                'definition' => 'Planète Terre dans son ensemble',
                'altLabels' => ['Terre', 'Globe terrestre', 'Planète'],
                'level' => 1
            ]
        ];

        // Niveau 9 : Chefs-lieux d'arrondissements et autres localités importantes
        $cheflieuxArrondissements = [
            'OBALA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/obala-ville',
                'notation' => 'CMR.CE.LK.OBA.OBA',
                'prefLabel' => 'Obala',
                'definition' => 'Chef-lieu de l\'arrondissement d\'Obala, département de la Lékié',
                'level' => 9,
                'parent' => 'OBALA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '25 000 hab.'
                ]
            ],

            'EVODOULA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/evodoula-ville',
                'notation' => 'CMR.CE.LK.EVO.EVO',
                'prefLabel' => 'Evodoula',
                'definition' => 'Chef-lieu de l\'arrondissement d\'Evodoula, département de la Lékié',
                'level' => 9,
                'parent' => 'EVODOULA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '15 000 hab.'
                ]
            ],

            'POUMA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/pouma-ville',
                'notation' => 'CMR.LT.SM.POU.POU',
                'prefLabel' => 'Pouma',
                'definition' => 'Chef-lieu de l\'arrondissement de Pouma, département de la Sanaga-Maritime',
                'level' => 9,
                'parent' => 'POUMA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '18 000 hab.'
                ]
            ],

            'FOKOUE_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/fokoue-ville',
                'notation' => 'CMR.OU.ME.FOK.FOK',
                'prefLabel' => 'Fokoué',
                'definition' => 'Chef-lieu de l\'arrondissement de Fokoué, département de la Menoua',
                'level' => 9,
                'parent' => 'FOKOUE',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '20 000 hab.'
                ]
            ],

            'SANTCHOU_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/santchou-ville',
                'notation' => 'CMR.OU.ME.SAN.SAN',
                'prefLabel' => 'Santchou',
                'definition' => 'Chef-lieu de l\'arrondissement de Santchou, département de la Menoua',
                'level' => 9,
                'parent' => 'SANTCHOU',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '22 000 hab.'
                ]
            ],

            'PITOA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/pitoa-ville',
                'notation' => 'CMR.NO.BE.PIT.PIT',
                'prefLabel' => 'Pitoa',
                'definition' => 'Chef-lieu de l\'arrondissement de Pitoa, département de la Bénoué',
                'level' => 9,
                'parent' => 'PITOA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '30 000 hab.'
                ]
            ],

            'BOGO_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bogo-ville',
                'notation' => 'CMR.EN.DI.BOG.BOG',
                'prefLabel' => 'Bogo',
                'definition' => 'Chef-lieu de l\'arrondissement de Bogo, département du Diamaré',
                'level' => 9,
                'parent' => 'BOGO',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '35 000 hab.'
                ]
            ],

            'TUBAH_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tubah-ville',
                'notation' => 'CMR.NW.MZ.TUB.TUB',
                'prefLabel' => 'Tubah',
                'definition' => 'Chef-lieu de l\'arrondissement de Tubah, département du Mezam',
                'level' => 9,
                'parent' => 'TUBAH',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '40 000 hab.'
                ]
            ],

            'TIKO_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tiko-ville',
                'notation' => 'CMR.SW.FA.TIK.TIK',
                'prefLabel' => 'Tiko',
                'definition' => 'Chef-lieu de l\'arrondissement de Tiko, département du Fako',
                'level' => 9,
                'parent' => 'TIKO',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '55 000 hab.',
                    'activite_principale' => 'Plantation de palmiers à huile'
                ]
            ],

            'MUYUKA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/muyuka-ville',
                'notation' => 'CMR.SW.FA.MUY.MUY',
                'prefLabel' => 'Muyuka',
                'definition' => 'Chef-lieu de l\'arrondissement de Muyuka, département du Fako',
                'level' => 9,
                'parent' => 'MUYUKA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '25 000 hab.'
                ]
            ],

            'MBONGE_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbonge-ville',
                'notation' => 'CMR.SW.ME.MBO.MBO',
                'prefLabel' => 'Mbonge',
                'definition' => 'Chef-lieu de l\'arrondissement de Mbonge, département de la Meme',
                'level' => 9,
                'parent' => 'MBONGE',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '28 000 hab.'
                ]
            ],

            'CAMPO_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/campo-ville',
                'notation' => 'CMR.SU.OC.CAM.CAM',
                'prefLabel' => 'Campo',
                'definition' => 'Chef-lieu de l\'arrondissement de Campo, département de l\'Océan',
                'level' => 9,
                'parent' => 'CAMPO',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '12 000 hab.',
                    'activite_principale' => 'Pêche et agriculture'
                ]
            ],

            'LOLODORF_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/lolodorf-ville',
                'notation' => 'CMR.SU.OC.LOL.LOL',
                'prefLabel' => 'Lolodorf',
                'definition' => 'Chef-lieu de l\'arrondissement de Lolodorf, département de l\'Océan',
                'level' => 9,
                'parent' => 'LOLODORF',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '8 000 hab.'
                ]
            ],

            'MENGONG_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mengong-ville',
                'notation' => 'CMR.SU.MV.MEN.MEN',
                'prefLabel' => 'Mengong',
                'definition' => 'Chef-lieu de l\'arrondissement de Mengong, département de la Mvila',
                'level' => 9,
                'parent' => 'MENGONG',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '15 000 hab.'
                ]
            ],

            'BETARE_OYA_VILLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/betare-oya-ville',
                'notation' => 'CMR.ES.LD.BET.BET',
                'prefLabel' => 'Bétaré-Oya',
                'definition' => 'Chef-lieu de l\'arrondissement de Bétaré-Oya, département du Lom-et-Djérem',
                'level' => 9,
                'parent' => 'BETARE_OYA',
                'properties' => [
                    'statut' => 'Chef-lieu d\'arrondissement',
                    'population' => '20 000 hab.',
                    'activite_principale' => 'Orpaillage et agriculture'
                ]
            ],

            // Niveau 2 : Continents
            'AFRIQUE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/afrique',
                'notation' => 'GEO.02',
                'prefLabel' => 'Afrique',
                'definition' => 'Continent africain',
                'altLabels' => ['Continent africain'],
                'level' => 2,
                'parent' => 'MONDE'
            ],

            // Niveau 3 : Sous-régions africaines
            'AFRIQUE_SUBSAHARIENNE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/afrique-subsaharienne',
                'notation' => 'GEO.03.01',
                'prefLabel' => 'Afrique subsaharienne',
                'definition' => 'Partie de l\'Afrique située au sud du Sahara',
                'altLabels' => ['Afrique noire', 'Afrique au sud du Sahara'],
                'level' => 3,
                'parent' => 'AFRIQUE'
            ],

            'AFRIQUE_CENTRALE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/afrique-centrale',
                'notation' => 'GEO.03.02',
                'prefLabel' => 'Afrique centrale',
                'definition' => 'Région d\'Afrique centrale selon les Nations Unies',
                'altLabels' => ['Afrique du Centre'],
                'level' => 3,
                'parent' => 'AFRIQUE_SUBSAHARIENNE'
            ],

            // Niveau 4 : Pays
            'CAMEROUN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/cameroun',
                'notation' => 'CMR',
                'prefLabel' => 'Cameroun',
                'definition' => 'République du Cameroun, pays d\'Afrique centrale',
                'altLabels' => ['République du Cameroun', 'Cameroon'],
                'level' => 4,
                'parent' => 'AFRIQUE_CENTRALE',
                'properties' => [
                    'code_iso_alpha2' => 'CM',
                    'code_iso_alpha3' => 'CMR',
                    'capitale' => 'Yaoundé',
                    'superficie' => '475 442 km²',
                    'population' => '27 millions (est. 2023)'
                ]
            ]
        ];

        // Niveau 5 : Régions du Cameroun
        $regions = [
            'ADAMAOUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/adamaoua',
                'notation' => 'CMR.AD',
                'prefLabel' => 'Adamaoua',
                'definition' => 'Région de l\'Adamaoua, Cameroun',
                'altLabels' => ['Adamawa'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Ngaoundéré',
                    'superficie' => '63 701 km²'
                ]
            ],

            'CENTRE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/centre',
                'notation' => 'CMR.CE',
                'prefLabel' => 'Centre',
                'definition' => 'Région du Centre, Cameroun',
                'altLabels' => ['Region du Centre'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Yaoundé',
                    'superficie' => '68 953 km²'
                ]
            ],

            'EST' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/est',
                'notation' => 'CMR.ES',
                'prefLabel' => 'Est',
                'definition' => 'Région de l\'Est, Cameroun',
                'altLabels' => ['Region de l\'Est', 'Eastern Region'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Bertoua',
                    'superficie' => '109 002 km²'
                ]
            ],

            'EXTREME_NORD' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/extreme-nord',
                'notation' => 'CMR.EN',
                'prefLabel' => 'Extrême-Nord',
                'definition' => 'Région de l\'Extrême-Nord, Cameroun',
                'altLabels' => ['Far North', 'Region de l\'Extreme-Nord'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Maroua',
                    'superficie' => '34 263 km²'
                ]
            ],

            'LITTORAL' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/littoral',
                'notation' => 'CMR.LT',
                'prefLabel' => 'Littoral',
                'definition' => 'Région du Littoral, Cameroun',
                'altLabels' => ['Region du Littoral', 'Coastal Region'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Douala',
                    'superficie' => '20 248 km²'
                ]
            ],

            'NORD' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nord',
                'notation' => 'CMR.NO',
                'prefLabel' => 'Nord',
                'definition' => 'Région du Nord, Cameroun',
                'altLabels' => ['Region du Nord', 'Northern Region'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Garoua',
                    'superficie' => '66 090 km²'
                ]
            ],

            'NORD_OUEST' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nord-ouest',
                'notation' => 'CMR.NO',
                'prefLabel' => 'Nord-Ouest',
                'definition' => 'Région du Nord-Ouest, Cameroun',
                'altLabels' => ['Northwest', 'Region du Nord-Ouest'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Bamenda',
                    'superficie' => '17 300 km²'
                ]
            ],

            'OUEST' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ouest',
                'notation' => 'CMR.OU',
                'prefLabel' => 'Ouest',
                'definition' => 'Région de l\'Ouest, Cameroun',
                'altLabels' => ['Region de l\'Ouest', 'Western Region'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Bafoussam',
                    'superficie' => '13 892 km²'
                ]
            ],

            'SUD' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/sud',
                'notation' => 'CMR.SU',
                'prefLabel' => 'Sud',
                'definition' => 'Région du Sud, Cameroun',
                'altLabels' => ['Region du Sud', 'Southern Region'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Ebolowa',
                    'superficie' => '47 191 km²'
                ]
            ],

            'SUD_OUEST' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/sud-ouest',
                'notation' => 'CMR.SO',
                'prefLabel' => 'Sud-Ouest',
                'definition' => 'Région du Sud-Ouest, Cameroun',
                'altLabels' => ['Southwest', 'Region du Sud-Ouest'],
                'level' => 5,
                'parent' => 'CAMEROUN',
                'properties' => [
                    'chef_lieu' => 'Buea',
                    'superficie' => '25 410 km²'
                ]
            ]
        ];

        // Niveau 6 : Tous les départements du Cameroun (58 départements)
        $departements = [
            // Région de l'Adamaoua (3 départements)
            'DJEREM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/djerem',
                'notation' => 'CMR.AD.DJ',
                'prefLabel' => 'Djérem',
                'definition' => 'Département du Djérem, région de l\'Adamaoua',
                'level' => 6,
                'parent' => 'ADAMAOUA',
                'properties' => ['chef_lieu' => 'Tibati']
            ],

            'FARO_DEO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/faro-deo',
                'notation' => 'CMR.AD.FD',
                'prefLabel' => 'Faro-et-Déo',
                'definition' => 'Département du Faro-et-Déo, région de l\'Adamaoua',
                'level' => 6,
                'parent' => 'ADAMAOUA',
                'properties' => ['chef_lieu' => 'Tignère']
            ],

            'VINA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/vina',
                'notation' => 'CMR.AD.VI',
                'prefLabel' => 'Vina',
                'definition' => 'Département de la Vina, région de l\'Adamaoua',
                'level' => 6,
                'parent' => 'ADAMAOUA',
                'properties' => ['chef_lieu' => 'Ngaoundéré']
            ],

            // Région du Centre (10 départements)
            'HAUTE_SANAGA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/haute-sanaga',
                'notation' => 'CMR.CE.HS',
                'prefLabel' => 'Haute-Sanaga',
                'definition' => 'Département de la Haute-Sanaga, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Nanga-Eboko']
            ],

            'LEKIE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/lekie',
                'notation' => 'CMR.CE.LK',
                'prefLabel' => 'Lékié',
                'definition' => 'Département de la Lékié, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Monatélé']
            ],

            'MBAM_INOUBOU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbam-inoubou',
                'notation' => 'CMR.CE.MI',
                'prefLabel' => 'Mbam-et-Inoubou',
                'definition' => 'Département du Mbam-et-Inoubou, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Bafia']
            ],

            'MBAM_KIM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbam-kim',
                'notation' => 'CMR.CE.MK',
                'prefLabel' => 'Mbam-et-Kim',
                'definition' => 'Département du Mbam-et-Kim, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Ntui']
            ],

            'MEFOU_AFAMBA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mefou-afamba',
                'notation' => 'CMR.CE.MA',
                'prefLabel' => 'Méfou-et-Afamba',
                'definition' => 'Département du Méfou-et-Afamba, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Mfou']
            ],

            'MEFOU_AKONO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mefou-akono',
                'notation' => 'CMR.CE.MO',
                'prefLabel' => 'Méfou-et-Akono',
                'definition' => 'Département du Méfou-et-Akono, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Ngoumou']
            ],

            'MFOUNDI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mfoundi',
                'notation' => 'CMR.CE.MF',
                'prefLabel' => 'Mfoundi',
                'definition' => 'Département du Mfoundi, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Yaoundé']
            ],

            'NYONG_KELLE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nyong-kelle',
                'notation' => 'CMR.CE.NK',
                'prefLabel' => 'Nyong-et-Kéllé',
                'definition' => 'Département du Nyong-et-Kéllé, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Eséka']
            ],

            'NYONG_MFOUMOU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nyong-mfoumou',
                'notation' => 'CMR.CE.NM',
                'prefLabel' => 'Nyong-et-Mfoumou',
                'definition' => 'Département du Nyong-et-Mfoumou, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Akonolinga']
            ],

            'NYONG_SOO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nyong-soo',
                'notation' => 'CMR.CE.NS',
                'prefLabel' => 'Nyong-et-So\'o',
                'definition' => 'Département du Nyong-et-So\'o, région du Centre',
                'level' => 6,
                'parent' => 'CENTRE',
                'properties' => ['chef_lieu' => 'Mbalmayo']
            ],

            // Région de l'Est (4 départements)
            'BOUMBA_NGOKO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/boumba-ngoko',
                'notation' => 'CMR.ES.BN',
                'prefLabel' => 'Boumba-et-Ngoko',
                'definition' => 'Département de la Boumba-et-Ngoko, région de l\'Est',
                'level' => 6,
                'parent' => 'EST',
                'properties' => ['chef_lieu' => 'Yokadouma']
            ],

            'HAUT_NYONG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/haut-nyong',
                'notation' => 'CMR.ES.HN',
                'prefLabel' => 'Haut-Nyong',
                'definition' => 'Département du Haut-Nyong, région de l\'Est',
                'level' => 6,
                'parent' => 'EST',
                'properties' => ['chef_lieu' => 'Abong-Mbang']
            ],

            'KADEY' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kadey',
                'notation' => 'CMR.ES.KA',
                'prefLabel' => 'Kadey',
                'definition' => 'Département du Kadey, région de l\'Est',
                'level' => 6,
                'parent' => 'EST',
                'properties' => ['chef_lieu' => 'Batouri']
            ],

            'LOM_DJEREM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/lom-djerem',
                'notation' => 'CMR.ES.LD',
                'prefLabel' => 'Lom-et-Djérem',
                'definition' => 'Département du Lom-et-Djérem, région de l\'Est',
                'level' => 6,
                'parent' => 'EST',
                'properties' => ['chef_lieu' => 'Bertoua']
            ],

            // Région de l'Extrême-Nord (6 départements)
            'DIAMARE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/diamare',
                'notation' => 'CMR.EN.DI',
                'prefLabel' => 'Diamaré',
                'definition' => 'Département du Diamaré, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Maroua']
            ],

            'LOGONE_CHARI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/logone-chari',
                'notation' => 'CMR.EN.LC',
                'prefLabel' => 'Logone-et-Chari',
                'definition' => 'Département du Logone-et-Chari, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Kousséri']
            ],

            'MAYO_DANAY' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-danay',
                'notation' => 'CMR.EN.MD',
                'prefLabel' => 'Mayo-Danay',
                'definition' => 'Département du Mayo-Danay, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Yagoua']
            ],

            'MAYO_KANI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-kani',
                'notation' => 'CMR.EN.MK',
                'prefLabel' => 'Mayo-Kani',
                'definition' => 'Département du Mayo-Kani, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Kaélé']
            ],

            'MAYO_SAVA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-sava',
                'notation' => 'CMR.EN.MS',
                'prefLabel' => 'Mayo-Sava',
                'definition' => 'Département du Mayo-Sava, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Mora']
            ],

            'MAYO_TSANAGA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-tsanaga',
                'notation' => 'CMR.EN.MT',
                'prefLabel' => 'Mayo-Tsanaga',
                'definition' => 'Département du Mayo-Tsanaga, région de l\'Extrême-Nord',
                'level' => 6,
                'parent' => 'EXTREME_NORD',
                'properties' => ['chef_lieu' => 'Mokolo']
            ],

            // Région du Littoral (4 départements)
            'MOUNGO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/moungo',
                'notation' => 'CMR.LT.MO',
                'prefLabel' => 'Moungo',
                'definition' => 'Département du Moungo, région du Littoral',
                'level' => 6,
                'parent' => 'LITTORAL',
                'properties' => ['chef_lieu' => 'Nkongsamba']
            ],

            'NKAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nkam',
                'notation' => 'CMR.LT.NK',
                'prefLabel' => 'Nkam',
                'definition' => 'Département du Nkam, région du Littoral',
                'level' => 6,
                'parent' => 'LITTORAL',
                'properties' => ['chef_lieu' => 'Yabassi']
            ],

            'SANAGA_MARITIME' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/sanaga-maritime',
                'notation' => 'CMR.LT.SM',
                'prefLabel' => 'Sanaga-Maritime',
                'definition' => 'Département de la Sanaga-Maritime, région du Littoral',
                'level' => 6,
                'parent' => 'LITTORAL',
                'properties' => ['chef_lieu' => 'Edéa']
            ],

            'WOURI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/wouri',
                'notation' => 'CMR.LT.WO',
                'prefLabel' => 'Wouri',
                'definition' => 'Département du Wouri, région du Littoral',
                'level' => 6,
                'parent' => 'LITTORAL',
                'properties' => ['chef_lieu' => 'Douala']
            ],

            // Région du Nord (4 départements)
            'BENOUE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/benoue',
                'notation' => 'CMR.NO.BE',
                'prefLabel' => 'Bénoué',
                'definition' => 'Département de la Bénoué, région du Nord',
                'level' => 6,
                'parent' => 'NORD',
                'properties' => ['chef_lieu' => 'Garoua']
            ],

            'FARO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/faro',
                'notation' => 'CMR.NO.FA',
                'prefLabel' => 'Faro',
                'definition' => 'Département du Faro, région du Nord',
                'level' => 6,
                'parent' => 'NORD',
                'properties' => ['chef_lieu' => 'Poli']
            ],

            'MAYO_LOUTI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-louti',
                'notation' => 'CMR.NO.ML',
                'prefLabel' => 'Mayo-Louti',
                'definition' => 'Département du Mayo-Louti, région du Nord',
                'level' => 6,
                'parent' => 'NORD',
                'properties' => ['chef_lieu' => 'Guider']
            ],

            'MAYO_REY' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mayo-rey',
                'notation' => 'CMR.NO.MR',
                'prefLabel' => 'Mayo-Rey',
                'definition' => 'Département du Mayo-Rey, région du Nord',
                'level' => 6,
                'parent' => 'NORD',
                'properties' => ['chef_lieu' => 'Tcholliré']
            ],

            // Région du Nord-Ouest (7 départements)
            'BOYO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/boyo',
                'notation' => 'CMR.NW.BO',
                'prefLabel' => 'Boyo',
                'definition' => 'Département du Boyo, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Fundong']
            ],

            'BUI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bui',
                'notation' => 'CMR.NW.BU',
                'prefLabel' => 'Bui',
                'definition' => 'Département du Bui, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Kumbo']
            ],

            'DONGA_MANTUNG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/donga-mantung',
                'notation' => 'CMR.NW.DM',
                'prefLabel' => 'Donga-Mantung',
                'definition' => 'Département du Donga-Mantung, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Nkambe']
            ],

            'MENCHUM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/menchum',
                'notation' => 'CMR.NW.ME',
                'prefLabel' => 'Menchum',
                'definition' => 'Département du Menchum, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Wum']
            ],

            'MEZAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mezam',
                'notation' => 'CMR.NW.MZ',
                'prefLabel' => 'Mezam',
                'definition' => 'Département du Mezam, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Bamenda']
            ],

            'MOMO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/momo',
                'notation' => 'CMR.NW.MM',
                'prefLabel' => 'Momo',
                'definition' => 'Département du Momo, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Mbengwi']
            ],

            'NGO_KETUNJIA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngo-ketunjia',
                'notation' => 'CMR.NW.NK',
                'prefLabel' => 'Ngo-Ketunjia',
                'definition' => 'Département du Ngo-Ketunjia, région du Nord-Ouest',
                'level' => 6,
                'parent' => 'NORD_OUEST',
                'properties' => ['chef_lieu' => 'Ndop']
            ],

            // Région de l'Ouest (8 départements)
            'BAMBOUTOS' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bamboutos',
                'notation' => 'CMR.OU.BA',
                'prefLabel' => 'Bamboutos',
                'definition' => 'Département des Bamboutos, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Mbouda']
            ],

            'HAUT_NKAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/haut-nkam',
                'notation' => 'CMR.OU.HN',
                'prefLabel' => 'Haut-Nkam',
                'definition' => 'Département du Haut-Nkam, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Bafang']
            ],

            'HAUTS_PLATEAUX' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/hauts-plateaux',
                'notation' => 'CMR.OU.HP',
                'prefLabel' => 'Hauts-Plateaux',
                'definition' => 'Département des Hauts-Plateaux, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Baham']
            ],

            'KOUNG_KHI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/koung-khi',
                'notation' => 'CMR.OU.KK',
                'prefLabel' => 'Koung-Khi',
                'definition' => 'Département du Koung-Khi, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Bandjoun']
            ],

            'MENOUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/menoua',
                'notation' => 'CMR.OU.ME',
                'prefLabel' => 'Menoua',
                'definition' => 'Département de la Menoua, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Dschang']
            ],

            'MIFI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mifi',
                'notation' => 'CMR.OU.MI',
                'prefLabel' => 'Mifi',
                'definition' => 'Département du Mifi, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Bafoussam']
            ],

            'NDE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nde',
                'notation' => 'CMR.OU.ND',
                'prefLabel' => 'Ndé',
                'definition' => 'Département du Ndé, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Bangangté']
            ],

            'NOUN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/noun',
                'notation' => 'CMR.OU.NO',
                'prefLabel' => 'Noun',
                'definition' => 'Département du Noun, région de l\'Ouest',
                'level' => 6,
                'parent' => 'OUEST',
                'properties' => ['chef_lieu' => 'Foumban']
            ],

            // Région du Sud (4 départements)
            'DJA_LOBO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/dja-lobo',
                'notation' => 'CMR.SU.DL',
                'prefLabel' => 'Dja-et-Lobo',
                'definition' => 'Département du Dja-et-Lobo, région du Sud',
                'level' => 6,
                'parent' => 'SUD',
                'properties' => ['chef_lieu' => 'Sangmélima']
            ],

            'MVILA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mvila',
                'notation' => 'CMR.SU.MV',
                'prefLabel' => 'Mvila',
                'definition' => 'Département de la Mvila, région du Sud',
                'level' => 6,
                'parent' => 'SUD',
                'properties' => ['chef_lieu' => 'Ebolowa']
            ],

            'OCEAN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ocean',
                'notation' => 'CMR.SU.OC',
                'prefLabel' => 'Océan',
                'definition' => 'Département de l\'Océan, région du Sud',
                'level' => 6,
                'parent' => 'SUD',
                'properties' => ['chef_lieu' => 'Kribi']
            ],

            'VALLEE_NTEM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/vallee-ntem',
                'notation' => 'CMR.SU.VN',
                'prefLabel' => 'Vallée-du-Ntem',
                'definition' => 'Département de la Vallée-du-Ntem, région du Sud',
                'level' => 6,
                'parent' => 'SUD',
                'properties' => ['chef_lieu' => 'Ambam']
            ],

            // Région du Sud-Ouest (6 départements)
            'FAKO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/fako',
                'notation' => 'CMR.SW.FA',
                'prefLabel' => 'Fako',
                'definition' => 'Département du Fako, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Limbe']
            ],

            'KOUPE_MANENGOUBA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/koupe-manengouba',
                'notation' => 'CMR.SW.KM',
                'prefLabel' => 'Koupé-Manengouba',
                'definition' => 'Département du Koupé-Manengouba, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Bangem']
            ],

            'LEBIALEM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/lebialem',
                'notation' => 'CMR.SW.LE',
                'prefLabel' => 'Lebialem',
                'definition' => 'Département du Lebialem, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Menji']
            ],

            'MANYU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/manyu',
                'notation' => 'CMR.SW.MA',
                'prefLabel' => 'Manyu',
                'definition' => 'Département du Manyu, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Mamfe']
            ],

            'MEME' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/meme',
                'notation' => 'CMR.SW.ME',
                'prefLabel' => 'Meme',
                'definition' => 'Département de la Meme, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Kumba']
            ],

            'NDIAN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ndian',
                'notation' => 'CMR.SW.ND',
                'prefLabel' => 'Ndian',
                'definition' => 'Département du Ndian, région du Sud-Ouest',
                'level' => 6,
                'parent' => 'SUD_OUEST',
                'properties' => ['chef_lieu' => 'Mundemba']
            ]
        ];

        // Niveau 7 : Villes principales
        $villes = [
            // Capitales et grandes métropoles
            'YAOUNDE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde',
                'notation' => 'CMR.CE.MF.YDE',
                'prefLabel' => 'Yaoundé',
                'definition' => 'Capitale politique du Cameroun, chef-lieu du département du Mfoundi',
                'altLabels' => ['Yaounde', 'Capitale du Cameroun'],
                'level' => 7,
                'parent' => 'MFOUNDI',
                'properties' => [
                    'statut' => 'Capitale nationale',
                    'population' => '4,2 millions (aire urbaine)',
                    'altitude' => '750 m'
                ]
            ],

            'DOUALA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala',
                'notation' => 'CMR.LT.WO.DLA',
                'prefLabel' => 'Douala',
                'definition' => 'Capitale économique du Cameroun, chef-lieu du département du Wouri',
                'altLabels' => ['Capitale économique', 'Port de Douala'],
                'level' => 7,
                'parent' => 'WOURI',
                'properties' => [
                    'statut' => 'Capitale économique',
                    'population' => '3,5 millions (aire urbaine)',
                    'activite_principale' => 'Port et industrie'
                ]
            ],

            // Chefs-lieux de régions
            'BAFOUSSAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafoussam',
                'notation' => 'CMR.OU.MI.BFS',
                'prefLabel' => 'Bafoussam',
                'definition' => 'Chef-lieu de la région de l\'Ouest',
                'level' => 7,
                'parent' => 'MIFI',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '400 000 hab.'
                ]
            ],

            'GAROUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/garoua',
                'notation' => 'CMR.NO.BE.GAR',
                'prefLabel' => 'Garoua',
                'definition' => 'Chef-lieu de la région du Nord',
                'level' => 7,
                'parent' => 'BENOUE',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '450 000 hab.'
                ]
            ],

            'MAROUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/maroua',
                'notation' => 'CMR.EN.DI.MAR',
                'prefLabel' => 'Maroua',
                'definition' => 'Chef-lieu de la région de l\'Extrême-Nord',
                'level' => 7,
                'parent' => 'DIAMARE',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '380 000 hab.'
                ]
            ],

            'BAMENDA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bamenda',
                'notation' => 'CMR.NO.BAM',
                'prefLabel' => 'Bamenda',
                'definition' => 'Chef-lieu de la région du Nord-Ouest',
                'level' => 7,
                'parent' => 'NORD_OUEST',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '500 000 hab.'
                ]
            ],

            'BUEA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/buea',
                'notation' => 'CMR.SO.BUE',
                'prefLabel' => 'Buea',
                'definition' => 'Chef-lieu de la région du Sud-Ouest',
                'level' => 7,
                'parent' => 'SUD_OUEST',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '200 000 hab.',
                    'altitude' => '1000 m'
                ]
            ],

            // Autres villes importantes
            'NGAOUNDERE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngaoundere',
                'notation' => 'CMR.AD.NGA',
                'prefLabel' => 'Ngaoundéré',
                'definition' => 'Chef-lieu de la région de l\'Adamaoua',
                'altLabels' => ['Ngaoundere'],
                'level' => 7,
                'parent' => 'ADAMAOUA',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '350 000 hab.',
                    'altitude' => '1100 m'
                ]
            ],

            'BERTOUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bertoua',
                'notation' => 'CMR.ES.BER',
                'prefLabel' => 'Bertoua',
                'definition' => 'Chef-lieu de la région de l\'Est',
                'level' => 7,
                'parent' => 'EST',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '220 000 hab.'
                ]
            ],

            'EBOLOWA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ebolowa',
                'notation' => 'CMR.SU.EBO',
                'prefLabel' => 'Ebolowa',
                'definition' => 'Chef-lieu de la région du Sud',
                'level' => 7,
                'parent' => 'SUD',
                'properties' => [
                    'statut' => 'Chef-lieu de région',
                    'population' => '180 000 hab.'
                ]
            ],

            'DSCHANG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/dschang',
                'notation' => 'CMR.OU.ME.DSC',
                'prefLabel' => 'Dschang',
                'definition' => 'Chef-lieu du département de la Menoua, ville universitaire',
                'level' => 7,
                'parent' => 'MENOUA',
                'properties' => [
                    'statut' => 'Chef-lieu de département',
                    'population' => '120 000 hab.',
                    'activite_principale' => 'Université et agriculture'
                ]
            ],

            'EDEA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/edea',
                'notation' => 'CMR.LT.SM.EDE',
                'prefLabel' => 'Edéa',
                'definition' => 'Chef-lieu du département de la Sanaga-Maritime, centre industriel',
                'altLabels' => ['Edea'],
                'level' => 7,
                'parent' => 'SANAGA_MARITIME',
                'properties' => [
                    'statut' => 'Chef-lieu de département',
                    'population' => '170 000 hab.',
                    'activite_principale' => 'Industrie (aluminium)'
                ]
            ],

            'KRIBI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kribi',
                'notation' => 'CMR.SU.OC.KRI',
                'prefLabel' => 'Kribi',
                'definition' => 'Chef-lieu du département de l\'Océan, ville portuaire et touristique',
                'level' => 7,
                'parent' => 'OCEAN',
                'properties' => [
                    'statut' => 'Chef-lieu de département',
                    'population' => '90 000 hab.',
                    'activite_principale' => 'Port en eau profonde et tourisme'
                ]
            ],

            // Autres chefs-lieux de départements
            'TIBATI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tibati',
                'notation' => 'CMR.AD.DJ.TIB',
                'prefLabel' => 'Tibati',
                'definition' => 'Chef-lieu du département du Djérem',
                'level' => 7,
                'parent' => 'DJEREM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'TIGNERE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tignere',
                'notation' => 'CMR.AD.FD.TIG',
                'prefLabel' => 'Tignère',
                'definition' => 'Chef-lieu du département du Faro-et-Déo',
                'level' => 7,
                'parent' => 'FARO_DEO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NANGA_EBOKO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nanga-eboko',
                'notation' => 'CMR.CE.HS.NEB',
                'prefLabel' => 'Nanga-Eboko',
                'definition' => 'Chef-lieu du département de la Haute-Sanaga',
                'level' => 7,
                'parent' => 'HAUTE_SANAGA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MONATELE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/monatele',
                'notation' => 'CMR.CE.LK.MON',
                'prefLabel' => 'Monatélé',
                'definition' => 'Chef-lieu du département de la Lékié',
                'level' => 7,
                'parent' => 'LEKIE',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BAFIA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafia',
                'notation' => 'CMR.CE.MI.BAF',
                'prefLabel' => 'Bafia',
                'definition' => 'Chef-lieu du département du Mbam-et-Inoubou',
                'level' => 7,
                'parent' => 'MBAM_INOUBOU',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NTUI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ntui',
                'notation' => 'CMR.CE.MK.NTU',
                'prefLabel' => 'Ntui',
                'definition' => 'Chef-lieu du département du Mbam-et-Kim',
                'level' => 7,
                'parent' => 'MBAM_KIM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MFOU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mfou',
                'notation' => 'CMR.CE.MA.MFO',
                'prefLabel' => 'Mfou',
                'definition' => 'Chef-lieu du département du Méfou-et-Afamba',
                'level' => 7,
                'parent' => 'MEFOU_AFAMBA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NGOUMOU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngoumou',
                'notation' => 'CMR.CE.MO.NGO',
                'prefLabel' => 'Ngoumou',
                'definition' => 'Chef-lieu du département du Méfou-et-Akono',
                'level' => 7,
                'parent' => 'MEFOU_AKONO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'ESEKA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/eseka',
                'notation' => 'CMR.CE.NK.ESE',
                'prefLabel' => 'Eséka',
                'definition' => 'Chef-lieu du département du Nyong-et-Kéllé',
                'level' => 7,
                'parent' => 'NYONG_KELLE',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'AKONOLINGA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/akonolinga',
                'notation' => 'CMR.CE.NM.AKO',
                'prefLabel' => 'Akonolinga',
                'definition' => 'Chef-lieu du département du Nyong-et-Mfoumou',
                'level' => 7,
                'parent' => 'NYONG_MFOUMOU',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MBALMAYO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbalmayo',
                'notation' => 'CMR.CE.NS.MBA',
                'prefLabel' => 'Mbalmayo',
                'definition' => 'Chef-lieu du département du Nyong-et-So\'o',
                'level' => 7,
                'parent' => 'NYONG_SOO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'YOKADOUMA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yokadouma',
                'notation' => 'CMR.ES.BN.YOK',
                'prefLabel' => 'Yokadouma',
                'definition' => 'Chef-lieu du département de la Boumba-et-Ngoko',
                'level' => 7,
                'parent' => 'BOUMBA_NGOKO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'ABONG_MBANG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/abong-mbang',
                'notation' => 'CMR.ES.HN.ABO',
                'prefLabel' => 'Abong-Mbang',
                'definition' => 'Chef-lieu du département du Haut-Nyong',
                'level' => 7,
                'parent' => 'HAUT_NYONG',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BATOURI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/batouri',
                'notation' => 'CMR.ES.KA.BAT',
                'prefLabel' => 'Batouri',
                'definition' => 'Chef-lieu du département du Kadey',
                'level' => 7,
                'parent' => 'KADEY',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'KOUSSERI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kousseri',
                'notation' => 'CMR.EN.LC.KOU',
                'prefLabel' => 'Kousséri',
                'definition' => 'Chef-lieu du département du Logone-et-Chari',
                'level' => 7,
                'parent' => 'LOGONE_CHARI',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'YAGOUA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yagoua',
                'notation' => 'CMR.EN.MD.YAG',
                'prefLabel' => 'Yagoua',
                'definition' => 'Chef-lieu du département du Mayo-Danay',
                'level' => 7,
                'parent' => 'MAYO_DANAY',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'KAELE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kaele',
                'notation' => 'CMR.EN.MK.KAE',
                'prefLabel' => 'Kaélé',
                'definition' => 'Chef-lieu du département du Mayo-Kani',
                'level' => 7,
                'parent' => 'MAYO_KANI',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MORA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mora',
                'notation' => 'CMR.EN.MS.MOR',
                'prefLabel' => 'Mora',
                'definition' => 'Chef-lieu du département du Mayo-Sava',
                'level' => 7,
                'parent' => 'MAYO_SAVA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MOKOLO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mokolo',
                'notation' => 'CMR.EN.MT.MOK',
                'prefLabel' => 'Mokolo',
                'definition' => 'Chef-lieu du département du Mayo-Tsanaga',
                'level' => 7,
                'parent' => 'MAYO_TSANAGA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NKONGSAMBA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nkongsamba',
                'notation' => 'CMR.LT.MO.NKO',
                'prefLabel' => 'Nkongsamba',
                'definition' => 'Chef-lieu du département du Moungo',
                'level' => 7,
                'parent' => 'MOUNGO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'YABASSI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yabassi',
                'notation' => 'CMR.LT.NK.YAB',
                'prefLabel' => 'Yabassi',
                'definition' => 'Chef-lieu du département du Nkam',
                'level' => 7,
                'parent' => 'NKAM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'POLI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/poli',
                'notation' => 'CMR.NO.FA.POL',
                'prefLabel' => 'Poli',
                'definition' => 'Chef-lieu du département du Faro',
                'level' => 7,
                'parent' => 'FARO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'GUIDER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/guider',
                'notation' => 'CMR.NO.ML.GUI',
                'prefLabel' => 'Guider',
                'definition' => 'Chef-lieu du département du Mayo-Louti',
                'level' => 7,
                'parent' => 'MAYO_LOUTI',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'TCHOLLIRE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tchollire',
                'notation' => 'CMR.NO.MR.TCH',
                'prefLabel' => 'Tcholliré',
                'definition' => 'Chef-lieu du département du Mayo-Rey',
                'level' => 7,
                'parent' => 'MAYO_REY',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'FUNDONG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/fundong',
                'notation' => 'CMR.NW.BO.FUN',
                'prefLabel' => 'Fundong',
                'definition' => 'Chef-lieu du département du Boyo',
                'level' => 7,
                'parent' => 'BOYO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'KUMBO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kumbo',
                'notation' => 'CMR.NW.BU.KUM',
                'prefLabel' => 'Kumbo',
                'definition' => 'Chef-lieu du département du Bui',
                'level' => 7,
                'parent' => 'BUI',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NKAMBE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/nkambe',
                'notation' => 'CMR.NW.DM.NKA',
                'prefLabel' => 'Nkambe',
                'definition' => 'Chef-lieu du département du Donga-Mantung',
                'level' => 7,
                'parent' => 'DONGA_MANTUNG',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'WUM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/wum',
                'notation' => 'CMR.NW.ME.WUM',
                'prefLabel' => 'Wum',
                'definition' => 'Chef-lieu du département du Menchum',
                'level' => 7,
                'parent' => 'MENCHUM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MBENGWI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbengwi',
                'notation' => 'CMR.NW.MM.MBE',
                'prefLabel' => 'Mbengwi',
                'definition' => 'Chef-lieu du département du Momo',
                'level' => 7,
                'parent' => 'MOMO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'NDOP' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ndop',
                'notation' => 'CMR.NW.NK.NDO',
                'prefLabel' => 'Ndop',
                'definition' => 'Chef-lieu du département du Ngo-Ketunjia',
                'level' => 7,
                'parent' => 'NGO_KETUNJIA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MBOUDA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbouda',
                'notation' => 'CMR.OU.BA.MBO',
                'prefLabel' => 'Mbouda',
                'definition' => 'Chef-lieu du département des Bamboutos',
                'level' => 7,
                'parent' => 'BAMBOUTOS',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BAFANG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafang',
                'notation' => 'CMR.OU.HN.BAF',
                'prefLabel' => 'Bafang',
                'definition' => 'Chef-lieu du département du Haut-Nkam',
                'level' => 7,
                'parent' => 'HAUT_NKAM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BAHAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/baham',
                'notation' => 'CMR.OU.HP.BAH',
                'prefLabel' => 'Baham',
                'definition' => 'Chef-lieu du département des Hauts-Plateaux',
                'level' => 7,
                'parent' => 'HAUTS_PLATEAUX',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BANDJOUN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bandjoun',
                'notation' => 'CMR.OU.KK.BAN',
                'prefLabel' => 'Bandjoun',
                'definition' => 'Chef-lieu du département du Koung-Khi',
                'level' => 7,
                'parent' => 'KOUNG_KHI',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'BANGANGTE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bangangte',
                'notation' => 'CMR.OU.ND.BNG',
                'prefLabel' => 'Bangangté',
                'definition' => 'Chef-lieu du département du Ndé',
                'level' => 7,
                'parent' => 'NDE',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'FOUMBAN' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/foumban',
                'notation' => 'CMR.OU.NO.FOU',
                'prefLabel' => 'Foumban',
                'definition' => 'Chef-lieu du département du Noun, ancienne capitale du royaume Bamoun',
                'level' => 7,
                'parent' => 'NOUN',
                'properties' => [
                    'statut' => 'Chef-lieu de département',
                    'particularite' => 'Ancienne capitale du royaume Bamoun'
                ]
            ],

            'SANGMELIMA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/sangmelima',
                'notation' => 'CMR.SU.DL.SAN',
                'prefLabel' => 'Sangmélima',
                'definition' => 'Chef-lieu du département du Dja-et-Lobo',
                'level' => 7,
                'parent' => 'DJA_LOBO',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'AMBAM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ambam',
                'notation' => 'CMR.SU.VN.AMB',
                'prefLabel' => 'Ambam',
                'definition' => 'Chef-lieu du département de la Vallée-du-Ntem',
                'level' => 7,
                'parent' => 'VALLEE_NTEM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'LIMBE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/limbe',
                'notation' => 'CMR.SW.FA.LIM',
                'prefLabel' => 'Limbe',
                'definition' => 'Chef-lieu du département du Fako, ville côtière',
                'altLabels' => ['Victoria'],
                'level' => 7,
                'parent' => 'FAKO',
                'properties' => [
                    'statut' => 'Chef-lieu de département',
                    'activite_principale' => 'Port et tourisme'
                ]
            ],

            'BANGEM' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bangem',
                'notation' => 'CMR.SW.KM.BNG',
                'prefLabel' => 'Bangem',
                'definition' => 'Chef-lieu du département du Koupé-Manengouba',
                'level' => 7,
                'parent' => 'KOUPE_MANENGOUBA',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MENJI' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/menji',
                'notation' => 'CMR.SW.LE.MEN',
                'prefLabel' => 'Menji',
                'definition' => 'Chef-lieu du département du Lebialem',
                'level' => 7,
                'parent' => 'LEBIALEM',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MAMFE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mamfe',
                'notation' => 'CMR.SW.MA.MAM',
                'prefLabel' => 'Mamfe',
                'definition' => 'Chef-lieu du département du Manyu',
                'level' => 7,
                'parent' => 'MANYU',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'KUMBA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kumba',
                'notation' => 'CMR.SW.ME.KUB',
                'prefLabel' => 'Kumba',
                'definition' => 'Chef-lieu du département de la Meme',
                'level' => 7,
                'parent' => 'MEME',
                'properties' => ['statut' => 'Chef-lieu de département']
            ],

            'MUNDEMBA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mundemba',
                'notation' => 'CMR.SW.ND.MUN',
                'prefLabel' => 'Mundemba',
                'definition' => 'Chef-lieu du département du Ndian',
                'level' => 7,
                'parent' => 'NDIAN',
                'properties' => ['statut' => 'Chef-lieu de département']
            ]
        ];

        // Niveau 8 : Arrondissements principaux (sélection représentative)
        $arrondissements = [
            // Région de l'Adamaoua - Département de la Vina
            'NGAOUNDERE_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngaoundere-1er',
                'notation' => 'CMR.AD.VI.NG1',
                'prefLabel' => 'Ngaoundéré 1er',
                'definition' => 'Arrondissement de Ngaoundéré 1er, département de la Vina',
                'level' => 8,
                'parent' => 'VINA',
                'properties' => ['chef_lieu' => 'Ngaoundéré', 'type' => 'Urbain']
            ],

            'NGAOUNDERE_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngaoundere-2e',
                'notation' => 'CMR.AD.VI.NG2',
                'prefLabel' => 'Ngaoundéré 2e',
                'definition' => 'Arrondissement de Ngaoundéré 2e, département de la Vina',
                'level' => 8,
                'parent' => 'VINA',
                'properties' => ['chef_lieu' => 'Ngaoundéré', 'type' => 'Urbain']
            ],

            'NGAOUNDERE_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ngaoundere-3e',
                'notation' => 'CMR.AD.VI.NG3',
                'prefLabel' => 'Ngaoundéré 3e',
                'definition' => 'Arrondissement de Ngaoundéré 3e, département de la Vina',
                'level' => 8,
                'parent' => 'VINA',
                'properties' => ['chef_lieu' => 'Ngaoundéré', 'type' => 'Urbain']
            ],

            'BELEL' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/belel',
                'notation' => 'CMR.AD.VI.BEL',
                'prefLabel' => 'Bélel',
                'definition' => 'Arrondissement de Bélel, département de la Vina',
                'level' => 8,
                'parent' => 'VINA',
                'properties' => ['chef_lieu' => 'Bélel', 'type' => 'Rural']
            ],

            // Région du Centre - Département du Mfoundi (Yaoundé)
            'YAOUNDE_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-1er',
                'notation' => 'CMR.CE.MF.YDE1',
                'prefLabel' => 'Yaoundé 1er',
                'definition' => 'Arrondissement de Yaoundé 1er, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-2e',
                'notation' => 'CMR.CE.MF.YDE2',
                'prefLabel' => 'Yaoundé 2e',
                'definition' => 'Arrondissement de Yaoundé 2e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-3e',
                'notation' => 'CMR.CE.MF.YDE3',
                'prefLabel' => 'Yaoundé 3e',
                'definition' => 'Arrondissement de Yaoundé 3e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_4E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-4e',
                'notation' => 'CMR.CE.MF.YDE4',
                'prefLabel' => 'Yaoundé 4e',
                'definition' => 'Arrondissement de Yaoundé 4e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_5E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-5e',
                'notation' => 'CMR.CE.MF.YDE5',
                'prefLabel' => 'Yaoundé 5e',
                'definition' => 'Arrondissement de Yaoundé 5e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_6E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-6e',
                'notation' => 'CMR.CE.MF.YDE6',
                'prefLabel' => 'Yaoundé 6e',
                'definition' => 'Arrondissement de Yaoundé 6e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            'YAOUNDE_7E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/yaounde-7e',
                'notation' => 'CMR.CE.MF.YDE7',
                'prefLabel' => 'Yaoundé 7e',
                'definition' => 'Arrondissement de Yaoundé 7e, département du Mfoundi',
                'level' => 8,
                'parent' => 'MFOUNDI',
                'properties' => ['chef_lieu' => 'Yaoundé', 'type' => 'Urbain']
            ],

            // Région du Centre - Département de la Lékié
            'MONATELE_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/monatele-arr',
                'notation' => 'CMR.CE.LK.MON',
                'prefLabel' => 'Monatélé',
                'definition' => 'Arrondissement de Monatélé, département de la Lékié',
                'level' => 8,
                'parent' => 'LEKIE',
                'properties' => ['chef_lieu' => 'Monatélé', 'type' => 'Rural']
            ],

            'OBALA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/obala',
                'notation' => 'CMR.CE.LK.OBA',
                'prefLabel' => 'Obala',
                'definition' => 'Arrondissement d\'Obala, département de la Lékié',
                'level' => 8,
                'parent' => 'LEKIE',
                'properties' => ['chef_lieu' => 'Obala', 'type' => 'Rural']
            ],

            'EVODOULA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/evodoula',
                'notation' => 'CMR.CE.LK.EVO',
                'prefLabel' => 'Evodoula',
                'definition' => 'Arrondissement d\'Evodoula, département de la Lékié',
                'level' => 8,
                'parent' => 'LEKIE',
                'properties' => ['chef_lieu' => 'Evodoula', 'type' => 'Rural']
            ],

            // Région du Littoral - Département du Wouri (Douala)
            'DOUALA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-1er',
                'notation' => 'CMR.LT.WO.DLA1',
                'prefLabel' => 'Douala 1er',
                'definition' => 'Arrondissement de Douala 1er, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            'DOUALA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-2e',
                'notation' => 'CMR.LT.WO.DLA2',
                'prefLabel' => 'Douala 2e',
                'definition' => 'Arrondissement de Douala 2e, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            'DOUALA_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-3e',
                'notation' => 'CMR.LT.WO.DLA3',
                'prefLabel' => 'Douala 3e',
                'definition' => 'Arrondissement de Douala 3e, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            'DOUALA_4E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-4e',
                'notation' => 'CMR.LT.WO.DLA4',
                'prefLabel' => 'Douala 4e',
                'definition' => 'Arrondissement de Douala 4e, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            'DOUALA_5E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-5e',
                'notation' => 'CMR.LT.WO.DLA5',
                'prefLabel' => 'Douala 5e',
                'definition' => 'Arrondissement de Douala 5e, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            'DOUALA_6E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/douala-6e',
                'notation' => 'CMR.LT.WO.DLA6',
                'prefLabel' => 'Douala 6e',
                'definition' => 'Arrondissement de Douala 6e, département du Wouri',
                'level' => 8,
                'parent' => 'WOURI',
                'properties' => ['chef_lieu' => 'Douala', 'type' => 'Urbain']
            ],

            // Région du Littoral - Département de la Sanaga-Maritime
            'EDEA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/edea-1er',
                'notation' => 'CMR.LT.SM.EDE1',
                'prefLabel' => 'Edéa 1er',
                'definition' => 'Arrondissement d\'Edéa 1er, département de la Sanaga-Maritime',
                'level' => 8,
                'parent' => 'SANAGA_MARITIME',
                'properties' => ['chef_lieu' => 'Edéa', 'type' => 'Urbain']
            ],

            'EDEA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/edea-2e',
                'notation' => 'CMR.LT.SM.EDE2',
                'prefLabel' => 'Edéa 2e',
                'definition' => 'Arrondissement d\'Edéa 2e, département de la Sanaga-Maritime',
                'level' => 8,
                'parent' => 'SANAGA_MARITIME',
                'properties' => ['chef_lieu' => 'Edéa', 'type' => 'Urbain']
            ],

            'POUMA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/pouma',
                'notation' => 'CMR.LT.SM.POU',
                'prefLabel' => 'Pouma',
                'definition' => 'Arrondissement de Pouma, département de la Sanaga-Maritime',
                'level' => 8,
                'parent' => 'SANAGA_MARITIME',
                'properties' => ['chef_lieu' => 'Pouma', 'type' => 'Rural']
            ],

            // Région de l'Ouest - Département du Mifi (Bafoussam)
            'BAFOUSSAM_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafoussam-1er',
                'notation' => 'CMR.OU.MI.BFS1',
                'prefLabel' => 'Bafoussam 1er',
                'definition' => 'Arrondissement de Bafoussam 1er, département du Mifi',
                'level' => 8,
                'parent' => 'MIFI',
                'properties' => ['chef_lieu' => 'Bafoussam', 'type' => 'Urbain']
            ],

            'BAFOUSSAM_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafoussam-2e',
                'notation' => 'CMR.OU.MI.BFS2',
                'prefLabel' => 'Bafoussam 2e',
                'definition' => 'Arrondissement de Bafoussam 2e, département du Mifi',
                'level' => 8,
                'parent' => 'MIFI',
                'properties' => ['chef_lieu' => 'Bafoussam', 'type' => 'Urbain']
            ],

            'BAFOUSSAM_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bafoussam-3e',
                'notation' => 'CMR.OU.MI.BFS3',
                'prefLabel' => 'Bafoussam 3e',
                'definition' => 'Arrondissement de Bafoussam 3e, département du Mifi',
                'level' => 8,
                'parent' => 'MIFI',
                'properties' => ['chef_lieu' => 'Bafoussam', 'type' => 'Urbain']
            ],

            // Région de l'Ouest - Département de la Menoua
            'DSCHANG_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/dschang-arr',
                'notation' => 'CMR.OU.ME.DSC',
                'prefLabel' => 'Dschang',
                'definition' => 'Arrondissement de Dschang, département de la Menoua',
                'level' => 8,
                'parent' => 'MENOUA',
                'properties' => ['chef_lieu' => 'Dschang', 'type' => 'Urbain']
            ],

            'FOKOUE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/fokoue',
                'notation' => 'CMR.OU.ME.FOK',
                'prefLabel' => 'Fokoué',
                'definition' => 'Arrondissement de Fokoué, département de la Menoua',
                'level' => 8,
                'parent' => 'MENOUA',
                'properties' => ['chef_lieu' => 'Fokoué', 'type' => 'Rural']
            ],

            'SANTCHOU' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/santchou',
                'notation' => 'CMR.OU.ME.SAN',
                'prefLabel' => 'Santchou',
                'definition' => 'Arrondissement de Santchou, département de la Menoua',
                'level' => 8,
                'parent' => 'MENOUA',
                'properties' => ['chef_lieu' => 'Santchou', 'type' => 'Rural']
            ],

            // Région du Nord - Département de la Bénoué
            'GAROUA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/garoua-1er',
                'notation' => 'CMR.NO.BE.GAR1',
                'prefLabel' => 'Garoua 1er',
                'definition' => 'Arrondissement de Garoua 1er, département de la Bénoué',
                'level' => 8,
                'parent' => 'BENOUE',
                'properties' => ['chef_lieu' => 'Garoua', 'type' => 'Urbain']
            ],

            'GAROUA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/garoua-2e',
                'notation' => 'CMR.NO.BE.GAR2',
                'prefLabel' => 'Garoua 2e',
                'definition' => 'Arrondissement de Garoua 2e, département de la Bénoué',
                'level' => 8,
                'parent' => 'BENOUE',
                'properties' => ['chef_lieu' => 'Garoua', 'type' => 'Urbain']
            ],

            'GAROUA_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/garoua-3e',
                'notation' => 'CMR.NO.BE.GAR3',
                'prefLabel' => 'Garoua 3e',
                'definition' => 'Arrondissement de Garoua 3e, département de la Bénoué',
                'level' => 8,
                'parent' => 'BENOUE',
                'properties' => ['chef_lieu' => 'Garoua', 'type' => 'Urbain']
            ],

            'PITOA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/pitoa',
                'notation' => 'CMR.NO.BE.PIT',
                'prefLabel' => 'Pitoa',
                'definition' => 'Arrondissement de Pitoa, département de la Bénoué',
                'level' => 8,
                'parent' => 'BENOUE',
                'properties' => ['chef_lieu' => 'Pitoa', 'type' => 'Rural']
            ],

            // Région de l'Extrême-Nord - Département du Diamaré
            'MAROUA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/maroua-1er',
                'notation' => 'CMR.EN.DI.MAR1',
                'prefLabel' => 'Maroua 1er',
                'definition' => 'Arrondissement de Maroua 1er, département du Diamaré',
                'level' => 8,
                'parent' => 'DIAMARE',
                'properties' => ['chef_lieu' => 'Maroua', 'type' => 'Urbain']
            ],

            'MAROUA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/maroua-2e',
                'notation' => 'CMR.EN.DI.MAR2',
                'prefLabel' => 'Maroua 2e',
                'definition' => 'Arrondissement de Maroua 2e, département du Diamaré',
                'level' => 8,
                'parent' => 'DIAMARE',
                'properties' => ['chef_lieu' => 'Maroua', 'type' => 'Urbain']
            ],

            'MAROUA_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/maroua-3e',
                'notation' => 'CMR.EN.DI.MAR3',
                'prefLabel' => 'Maroua 3e',
                'definition' => 'Arrondissement de Maroua 3e, département du Diamaré',
                'level' => 8,
                'parent' => 'DIAMARE',
                'properties' => ['chef_lieu' => 'Maroua', 'type' => 'Urbain']
            ],

            'BOGO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bogo',
                'notation' => 'CMR.EN.DI.BOG',
                'prefLabel' => 'Bogo',
                'definition' => 'Arrondissement de Bogo, département du Diamaré',
                'level' => 8,
                'parent' => 'DIAMARE',
                'properties' => ['chef_lieu' => 'Bogo', 'type' => 'Rural']
            ],

            // Région du Nord-Ouest - Département du Mezam
            'BAMENDA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bamenda-1er',
                'notation' => 'CMR.NW.MZ.BAM1',
                'prefLabel' => 'Bamenda 1er',
                'definition' => 'Arrondissement de Bamenda 1er, département du Mezam',
                'level' => 8,
                'parent' => 'MEZAM',
                'properties' => ['chef_lieu' => 'Bamenda', 'type' => 'Urbain']
            ],

            'BAMENDA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bamenda-2e',
                'notation' => 'CMR.NW.MZ.BAM2',
                'prefLabel' => 'Bamenda 2e',
                'definition' => 'Arrondissement de Bamenda 2e, département du Mezam',
                'level' => 8,
                'parent' => 'MEZAM',
                'properties' => ['chef_lieu' => 'Bamenda', 'type' => 'Urbain']
            ],

            'BAMENDA_3E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bamenda-3e',
                'notation' => 'CMR.NW.MZ.BAM3',
                'prefLabel' => 'Bamenda 3e',
                'definition' => 'Arrondissement de Bamenda 3e, département du Mezam',
                'level' => 8,
                'parent' => 'MEZAM',
                'properties' => ['chef_lieu' => 'Bamenda', 'type' => 'Urbain']
            ],

            'TUBAH' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tubah',
                'notation' => 'CMR.NW.MZ.TUB',
                'prefLabel' => 'Tubah',
                'definition' => 'Arrondissement de Tubah, département du Mezam',
                'level' => 8,
                'parent' => 'MEZAM',
                'properties' => ['chef_lieu' => 'Tubah', 'type' => 'Rural']
            ],

            // Région du Sud-Ouest - Département du Fako
            'LIMBE_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/limbe-arr',
                'notation' => 'CMR.SW.FA.LIM',
                'prefLabel' => 'Limbe',
                'definition' => 'Arrondissement de Limbe, département du Fako',
                'level' => 8,
                'parent' => 'FAKO',
                'properties' => ['chef_lieu' => 'Limbe', 'type' => 'Urbain']
            ],

            'BUEA_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/buea-arr',
                'notation' => 'CMR.SW.FA.BUE',
                'prefLabel' => 'Buea',
                'definition' => 'Arrondissement de Buea, département du Fako',
                'level' => 8,
                'parent' => 'FAKO',
                'properties' => ['chef_lieu' => 'Buea', 'type' => 'Urbain']
            ],

            'TIKO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/tiko',
                'notation' => 'CMR.SW.FA.TIK',
                'prefLabel' => 'Tiko',
                'definition' => 'Arrondissement de Tiko, département du Fako',
                'level' => 8,
                'parent' => 'FAKO',
                'properties' => ['chef_lieu' => 'Tiko', 'type' => 'Rural']
            ],

            'MUYUKA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/muyuka',
                'notation' => 'CMR.SW.FA.MUY',
                'prefLabel' => 'Muyuka',
                'definition' => 'Arrondissement de Muyuka, département du Fako',
                'level' => 8,
                'parent' => 'FAKO',
                'properties' => ['chef_lieu' => 'Muyuka', 'type' => 'Rural']
            ],

            // Région du Sud-Ouest - Département de la Meme
            'KUMBA_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kumba-arr',
                'notation' => 'CMR.SW.ME.KUB',
                'prefLabel' => 'Kumba',
                'definition' => 'Arrondissement de Kumba, département de la Meme',
                'level' => 8,
                'parent' => 'MEME',
                'properties' => ['chef_lieu' => 'Kumba', 'type' => 'Urbain']
            ],

            'MBONGE' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mbonge',
                'notation' => 'CMR.SW.ME.MBO',
                'prefLabel' => 'Mbonge',
                'definition' => 'Arrondissement de Mbonge, département de la Meme',
                'level' => 8,
                'parent' => 'MEME',
                'properties' => ['chef_lieu' => 'Mbonge', 'type' => 'Rural']
            ],

            // Région du Sud - Département de l'Océan
            'KRIBI_ARR' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/kribi-arr',
                'notation' => 'CMR.SU.OC.KRI',
                'prefLabel' => 'Kribi',
                'definition' => 'Arrondissement de Kribi, département de l\'Océan',
                'level' => 8,
                'parent' => 'OCEAN',
                'properties' => ['chef_lieu' => 'Kribi', 'type' => 'Urbain']
            ],

            'CAMPO' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/campo',
                'notation' => 'CMR.SU.OC.CAM',
                'prefLabel' => 'Campo',
                'definition' => 'Arrondissement de Campo, département de l\'Océan',
                'level' => 8,
                'parent' => 'OCEAN',
                'properties' => ['chef_lieu' => 'Campo', 'type' => 'Rural']
            ],

            'LOLODORF' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/lolodorf',
                'notation' => 'CMR.SU.OC.LOL',
                'prefLabel' => 'Lolodorf',
                'definition' => 'Arrondissement de Lolodorf, département de l\'Océan',
                'level' => 8,
                'parent' => 'OCEAN',
                'properties' => ['chef_lieu' => 'Lolodorf', 'type' => 'Rural']
            ],

            // Région du Sud - Département de la Mvila
            'EBOLOWA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ebolowa-1er',
                'notation' => 'CMR.SU.MV.EBO1',
                'prefLabel' => 'Ebolowa 1er',
                'definition' => 'Arrondissement d\'Ebolowa 1er, département de la Mvila',
                'level' => 8,
                'parent' => 'MVILA',
                'properties' => ['chef_lieu' => 'Ebolowa', 'type' => 'Urbain']
            ],

            'EBOLOWA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/ebolowa-2e',
                'notation' => 'CMR.SU.MV.EBO2',
                'prefLabel' => 'Ebolowa 2e',
                'definition' => 'Arrondissement d\'Ebolowa 2e, département de la Mvila',
                'level' => 8,
                'parent' => 'MVILA',
                'properties' => ['chef_lieu' => 'Ebolowa', 'type' => 'Urbain']
            ],

            'MENGONG' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/mengong',
                'notation' => 'CMR.SU.MV.MEN',
                'prefLabel' => 'Mengong',
                'definition' => 'Arrondissement de Mengong, département de la Mvila',
                'level' => 8,
                'parent' => 'MVILA',
                'properties' => ['chef_lieu' => 'Mengong', 'type' => 'Rural']
            ],

            // Région de l'Est - Département du Lom-et-Djérem
            'BERTOUA_1ER' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bertoua-1er',
                'notation' => 'CMR.ES.LD.BER1',
                'prefLabel' => 'Bertoua 1er',
                'definition' => 'Arrondissement de Bertoua 1er, département du Lom-et-Djérem',
                'level' => 8,
                'parent' => 'LOM_DJEREM',
                'properties' => ['chef_lieu' => 'Bertoua', 'type' => 'Urbain']
            ],

            'BERTOUA_2E' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/bertoua-2e',
                'notation' => 'CMR.ES.LD.BER2',
                'prefLabel' => 'Bertoua 2e',
                'definition' => 'Arrondissement de Bertoua 2e, département du Lom-et-Djérem',
                'level' => 8,
                'parent' => 'LOM_DJEREM',
                'properties' => ['chef_lieu' => 'Bertoua', 'type' => 'Urbain']
            ],

            'BETARE_OYA' => [
                'uri' => 'https://geo.cameroun.cm/thesaurus/betare-oya',
                'notation' => 'CMR.ES.LD.BET',
                'prefLabel' => 'Bétaré-Oya',
                'definition' => 'Arrondissement de Bétaré-Oya, département du Lom-et-Djérem',
                'level' => 8,
                'parent' => 'LOM_DJEREM',
                'properties' => ['chef_lieu' => 'Bétaré-Oya', 'type' => 'Rural']
            ]
        ];

        // Combiner tous les concepts
        $allConcepts = array_merge(
            $geoHierarchy,
            $regions,
            $departements,
            $arrondissements,
            $villes
        );

        $conceptIds = [];

        // Insérer tous les concepts
        foreach ($allConcepts as $key => $concept) {
            // Insérer si absent (clé unique: uri)
            DB::table('thesaurus_concepts')->insertOrIgnore([
                'scheme_id' => $schemeId,
                'uri' => $concept['uri'],
                'notation' => $concept['notation'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Récupérer l'ID du concept par son URI
            $conceptRow = DB::table('thesaurus_concepts')->where('uri', $concept['uri'])->first();
            if (!$conceptRow) { continue; }
            $conceptIds[$key] = $conceptRow->id;

            // Ajouter le label préféré
            DB::table('thesaurus_labels')->insertOrIgnore([
                'concept_id' => $conceptIds[$key],
                'type' => 'prefLabel',
                'literal_form' => $concept['prefLabel'],
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ajouter la définition
            if (isset($concept['definition'])) {
                DB::table('thesaurus_concept_notes')->insertOrIgnore([
                    'concept_id' => $conceptIds[$key],
                    'type' => 'definition',
                    'note' => $concept['definition'],
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ajouter les labels alternatifs
            if (isset($concept['altLabels'])) {
                foreach ($concept['altLabels'] as $altLabel) {
                    DB::table('thesaurus_labels')->insertOrIgnore([
                        'concept_id' => $conceptIds[$key],
                        'type' => 'altLabel',
                        'literal_form' => $altLabel,
                        'language' => 'fr-fr',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Ajouter les propriétés spécifiques
            if (isset($concept['properties'])) {
                foreach ($concept['properties'] as $propName => $propValue) {
                    DB::table('thesaurus_concept_properties')->insertOrIgnore([
                        'concept_id' => $conceptIds[$key],
                        'property_name' => $propName,
                        'property_value' => $propValue,
                        'language' => 'fr-fr',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Créer les relations hiérarchiques
        foreach ($allConcepts as $key => $concept) {
            if (isset($concept['parent']) && isset($conceptIds[$concept['parent']])) {
                // Relation broader (le concept a un terme plus large)
                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $conceptIds[$key],
                    'related_concept_id' => $conceptIds[$concept['parent']],
                    'relation_type' => 'broader',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Relation narrower (le concept parent a un terme plus spécifique)
                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $conceptIds[$concept['parent']],
                    'related_concept_id' => $conceptIds[$key],
                    'relation_type' => 'narrower',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ajouter des relations "related" entre entités du même niveau ou complémentaires
        $relatedConcepts = [
            ['YAOUNDE', 'DOUALA'], // Les deux principales métropoles
            ['NORD_OUEST', 'SUD_OUEST'], // Régions anglophones
            ['NORD', 'EXTREME_NORD'], // Régions du Grand Nord
            ['BAMENDA', 'BUEA'], // Chefs-lieux anglophones
        ];

        foreach ($relatedConcepts as $relation) {
            if (isset($conceptIds[$relation[0]]) && isset($conceptIds[$relation[1]])) {
                // Relations bidirectionnelles
                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $conceptIds[$relation[0]],
                    'related_concept_id' => $conceptIds[$relation[1]],
                    'relation_type' => 'related',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $conceptIds[$relation[1]],
                    'related_concept_id' => $conceptIds[$relation[0]],
                    'relation_type' => 'related',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ajouter des notes d'application géographiques
        $scopeNotes = [
            'CAMEROUN' => 'République unitaire décentralisée divisée en 10 régions. Membre de la CEMAC et du Commonwealth.',
            'YAOUNDE' => 'Siège des institutions nationales et de nombreuses organisations internationales. Divisée en 7 arrondissements urbains.',
            'DOUALA' => 'Principal port du Cameroun et de la sous-région Afrique centrale. Hub économique. Divisée en 6 arrondissements urbains.',
            'AFRIQUE_CENTRALE' => 'Selon la classification de l\'ONU : Cameroun, RCA, Tchad, Congo, RDC, Guinée équatoriale, Gabon, Sao Tomé-et-Principe.',
            'NORD_OUEST' => 'Région anglophone du Cameroun avec Bamenda comme chef-lieu.',
            'SUD_OUEST' => 'Région anglophone du Cameroun avec Buea comme chef-lieu.',
            'MFOUNDI' => 'Département urbain comprenant la capitale Yaoundé et ses 7 arrondissements.',
            'WOURI' => 'Département urbain comprenant Douala et ses 6 arrondissements, principal centre économique.',
            'ARRONDISSEMENTS' => 'Les arrondissements constituent la subdivision administrative de base au Cameroun. Chaque département est divisé en arrondissements dirigés par des sous-préfets. Les grandes villes sont divisées en plusieurs arrondissements urbains.'
        ];

        foreach ($scopeNotes as $conceptKey => $note) {
            if (isset($conceptIds[$conceptKey])) {
                DB::table('thesaurus_concept_notes')->insertOrIgnore([
                    'concept_id' => $conceptIds[$conceptKey],
                    'type' => 'scopeNote',
                    'note' => $note,
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ajouter l'organisation responsable
        // Ajouter l'organisation responsable (idempotent sur le nom)
        DB::table('thesaurus_organizations')->insertOrIgnore([
            'name' => 'Institut National de Cartographie du Cameroun',
            'homepage' => 'https://www.inc.cm',
            'email' => 'info@inc.cm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $org = DB::table('thesaurus_organizations')->where('name', 'Institut National de Cartographie du Cameroun')->first();
        $orgId = $org ? $org->id : null;

        // Ajouter les namespaces
        DB::table('thesaurus_namespaces')->insertOrIgnore([
            [
                'prefix' => 'geo',
                'namespace_uri' => 'https://geo.cameroun.cm/thesaurus/',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'geonames',
                'namespace_uri' => 'https://www.geonames.org/',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
