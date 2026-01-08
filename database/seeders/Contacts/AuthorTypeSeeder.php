<?php

namespace Database\Seeders\Contacts;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * BasÃ© sur les normes ISAD(G) et ISAAR(CPF) pour la description archivistique
     */
    public function run(): void
    {
        // Types d'auteurs conformes aux normes ISAD(G) et ISAAR(CPF)
        $authorTypes = [
            // Types de personnes selon ISAAR(CPF)
            [
                'name' => 'Personne physique',
                'description' => 'Individu identifiÃ© comme entitÃ© archivistique conforme Ã  ISAAR(CPF).',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'CollectivitÃ©',
                'description' => 'Organisation ou groupe de personnes identifiÃ© par un nom particulier et agissant comme une entitÃ©.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Famille',
                'description' => 'Personnes liÃ©es par le sang ou constituant lÃ©galement une famille.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Sous-types de collectivitÃ©s selon ISAAR(CPF)
            [
                'name' => 'Institution publique',
                'description' => 'Organisme gouvernemental ou administration publique.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Organisme international',
                'description' => 'Organisation avec une portÃ©e ou une activitÃ© internationale.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Entreprise privÃ©e',
                'description' => 'EntitÃ© commerciale ou sociÃ©tÃ© privÃ©e.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Association',
                'description' => 'Groupe formÃ© volontairement Ã  des fins sociales, professionnelles ou autres fins non lucratives.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Organisation religieuse',
                'description' => 'EntitÃ© liÃ©e Ã  des activitÃ©s religieuses ou confessionnelles.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Types spÃ©cifiques d'autoritÃ©s selon le contexte archivistique
            [
                'name' => 'Producteur d\'archives',
                'description' => 'EntitÃ© responsable de la crÃ©ation, accumulation et/ou conservation des documents dans l\'exercice de ses activitÃ©s.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Service d\'archives',
                'description' => 'Institution responsable de la conservation et de la gestion d\'archives.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'DÃ©tenteur de droits',
                'description' => 'EntitÃ© dÃ©tenant des droits de propriÃ©tÃ© intellectuelle ou autres droits sur les documents.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Autre entitÃ©',
                'description' => 'Autre type d\'auteur ne correspondant pas aux catÃ©gories dÃ©finies dans ISAAR(CPF).',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('author_types')->insertOrIgnore($authorTypes);
    }
}

