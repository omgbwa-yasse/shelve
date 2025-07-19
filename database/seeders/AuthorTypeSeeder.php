<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Basé sur les normes ISAD(G) et ISAAR(CPF) pour la description archivistique
     */
    public function run(): void
    {
        // Types d'auteurs conformes aux normes ISAD(G) et ISAAR(CPF)
        $authorTypes = [
            // Types de personnes selon ISAAR(CPF)
            [
                'name' => 'Personne physique',
                'description' => 'Individu identifié comme entité archivistique conforme à ISAAR(CPF).',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Collectivité',
                'description' => 'Organisation ou groupe de personnes identifié par un nom particulier et agissant comme une entité.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Famille',
                'description' => 'Personnes liées par le sang ou constituant légalement une famille.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Sous-types de collectivités selon ISAAR(CPF)
            [
                'name' => 'Institution publique',
                'description' => 'Organisme gouvernemental ou administration publique.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Organisme international',
                'description' => 'Organisation avec une portée ou une activité internationale.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Entreprise privée',
                'description' => 'Entité commerciale ou société privée.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Association',
                'description' => 'Groupe formé volontairement à des fins sociales, professionnelles ou autres fins non lucratives.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Organisation religieuse',
                'description' => 'Entité liée à des activités religieuses ou confessionnelles.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Types spécifiques d'autorités selon le contexte archivistique
            [
                'name' => 'Producteur d\'archives',
                'description' => 'Entité responsable de la création, accumulation et/ou conservation des documents dans l\'exercice de ses activités.',
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
                'name' => 'Détenteur de droits',
                'description' => 'Entité détenant des droits de propriété intellectuelle ou autres droits sur les documents.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Autre entité',
                'description' => 'Autre type d\'auteur ne correspondant pas aux catégories définies dans ISAAR(CPF).',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('author_types')->insert($authorTypes);
    }
}
