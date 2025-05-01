<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Create 20 demo users with different roles in different organizations
        $users = [
            // Super Admin
            [
                'id' => 1,
                'name' => 'Admin',
                'surname' => 'System',
                'birthday' => '1980-01-01',
                'email' => 'admin@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Organisation Admins
            [
                'id' => 2,
                'name' => 'Pierre',
                'surname' => 'Durand',
                'birthday' => '1975-05-15',
                'email' => 'pierre.durand@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Marie',
                'surname' => 'Lambert',
                'birthday' => '1982-08-22',
                'email' => 'marie.lambert@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Jean',
                'surname' => 'Moreau',
                'birthday' => '1978-11-30',
                'email' => 'jean.moreau@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Archivistes
            [
                'id' => 5,
                'name' => 'Sophie',
                'surname' => 'Martin',
                'birthday' => '1985-03-12',
                'email' => 'sophie.martin@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'Lucas',
                'surname' => 'Petit',
                'birthday' => '1990-07-08',
                'email' => 'lucas.petit@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'Emma',
                'surname' => 'Richard',
                'birthday' => '1988-12-20',
                'email' => 'emma.richard@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 12,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'name' => 'Thomas',
                'surname' => 'Bernard',
                'birthday' => '1983-02-25',
                'email' => 'thomas.bernard@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 13,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Producteurs
            [
                'id' => 9,
                'name' => 'Camille',
                'surname' => 'Dubois',
                'birthday' => '1992-04-18',
                'email' => 'camille.dubois@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'name' => 'Antoine',
                'surname' => 'Leroy',
                'birthday' => '1986-09-05',
                'email' => 'antoine.leroy@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'name' => 'Julie',
                'surname' => 'Girard',
                'birthday' => '1991-06-10',
                'email' => 'julie.girard@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'name' => 'Nicolas',
                'surname' => 'Blanc',
                'birthday' => '1987-01-29',
                'email' => 'nicolas.blanc@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 9,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 13,
                'name' => 'Elodie',
                'surname' => 'Rousseau',
                'birthday' => '1993-10-15',
                'email' => 'elodie.rousseau@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 14,
                'name' => 'Julien',
                'surname' => 'Fournier',
                'birthday' => '1984-08-07',
                'email' => 'julien.fournier@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 11,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Consulteurs
            [
                'id' => 15,
                'name' => 'Léa',
                'surname' => 'Robert',
                'birthday' => '1995-03-30',
                'email' => 'lea.robert@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 16,
                'name' => 'Maxime',
                'surname' => 'Simon',
                'birthday' => '1989-11-12',
                'email' => 'maxime.simon@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 17,
                'name' => 'Chloé',
                'surname' => 'Michel',
                'birthday' => '1994-05-22',
                'email' => 'chloe.michel@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 18,
                'name' => 'Hugo',
                'surname' => 'Lefebvre',
                'birthday' => '1981-07-17',
                'email' => 'hugo.lefebvre@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 19,
                'name' => 'Sarah',
                'surname' => 'Mercier',
                'birthday' => '1996-12-05',
                'email' => 'sarah.mercier@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 20,
                'name' => 'Quentin',
                'surname' => 'Garcia',
                'birthday' => '1979-09-18',
                'email' => 'quentin.garcia@demo.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'current_organisation_id' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // User-Organisation-Role relationships
        $userOrganisationRoles = [
            // Super admin - access to all organizations
            ['user_id' => 1, 'organisation_id' => 1, 'role_id' => 1, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Organisation admins
            ['user_id' => 2, 'organisation_id' => 2, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'organisation_id' => 3, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'organisation_id' => 4, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Archivistes
            ['user_id' => 5, 'organisation_id' => 5, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 6, 'organisation_id' => 5, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 7, 'organisation_id' => 12, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 8, 'organisation_id' => 13, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Producteurs
            ['user_id' => 9, 'organisation_id' => 6, 'role_id' => 4, 'creator_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 10, 'organisation_id' => 7, 'role_id' => 4, 'creator_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 11, 'organisation_id' => 8, 'role_id' => 4, 'creator_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 12, 'organisation_id' => 9, 'role_id' => 4, 'creator_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 13, 'organisation_id' => 10, 'role_id' => 4, 'creator_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 14, 'organisation_id' => 11, 'role_id' => 4, 'creator_id' => 4, 'created_at' => $now, 'updated_at' => $now],

            // Consulteurs
            ['user_id' => 15, 'organisation_id' => 2, 'role_id' => 5, 'creator_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 16, 'organisation_id' => 3, 'role_id' => 5, 'creator_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 17, 'organisation_id' => 4, 'role_id' => 5, 'creator_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 18, 'organisation_id' => 5, 'role_id' => 5, 'creator_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 19, 'organisation_id' => 6, 'role_id' => 5, 'creator_id' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 20, 'organisation_id' => 7, 'role_id' => 5, 'creator_id' => 10, 'created_at' => $now, 'updated_at' => $now],

            // Additional roles for cross-department access
            ['user_id' => 2, 'organisation_id' => 6, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'organisation_id' => 7, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'organisation_id' => 8, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'organisation_id' => 9, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'organisation_id' => 10, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'organisation_id' => 11, 'role_id' => 2, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 5, 'organisation_id' => 12, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 5, 'organisation_id' => 13, 'role_id' => 3, 'creator_id' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('users')->insert($users);
        DB::table('user_organisation_role')->insert($userOrganisationRoles);
    }
}
