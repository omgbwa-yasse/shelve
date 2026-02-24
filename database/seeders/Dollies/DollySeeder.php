<?php

namespace Database\Seeders\Dollies;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Dolly;
use App\Models\DollyType;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Mail;
use App\Models\RecordPhysical;
use App\Models\Communication;
use App\Models\Slip;
use App\Models\Container;

class DollySeeder extends Seeder
{
    /**
     * Seed test data for the Dollies (Chariots) module.
     * Creates dolly types, dollies of various categories, and pivot entries.
     * Idempotent: uses firstOrCreate/updateOrInsert.
     */
    public function run(): void
    {
        $this->command->info('🛒 Seeding Dollies module test data...');

        $user = User::first();
        $org = Organisation::first();

        if (!$user || !$org) {
            $this->command->warn('⚠️  No users/organisations found. Run UserSeeder first.');
            return;
        }

        // --- 1. Dolly Types ---
        $typeDefs = [
            ['name' => 'Chariot de consultation',  'description' => 'Chariot utilisé pour les demandes de communication en salle de lecture.'],
            ['name' => 'Chariot de versement',     'description' => 'Chariot pour le transfert de bordereaux de versement.'],
            ['name' => 'Chariot de classement',     'description' => 'Chariot temporaire pour le tri et classement de documents.'],
            ['name' => 'Chariot numérique',         'description' => 'Panier virtuel pour les documents numériques.'],
        ];

        foreach ($typeDefs as $td) {
            DollyType::firstOrCreate(['name' => $td['name']], $td);
        }

        // --- 2. Dollies (one per main category) ---
        $dollyDefs = [
            ['name' => 'Chariot courrier entrant',         'category' => 'mail',           'description' => 'Courriers reçus à traiter', 'is_public' => false],
            ['name' => 'Chariot transactions',              'category' => 'transaction',    'description' => 'Transactions en cours', 'is_public' => false],
            ['name' => 'Chariot archives physiques',        'category' => 'record',         'description' => 'Documents à classer', 'is_public' => false],
            ['name' => 'Chariot bordereaux',                'category' => 'slip',           'description' => 'Bordereaux de versement', 'is_public' => false],
            ['name' => 'Chariot communications',            'category' => 'communication',  'description' => 'Dossiers en communication', 'is_public' => false],
            ['name' => 'Chariot conteneurs',                'category' => 'container',      'description' => 'Conteneurs à ranger', 'is_public' => false],
            ['name' => 'Chariot bâtiments',                 'category' => 'building',       'description' => 'Bâtiments favoris', 'is_public' => false],
            ['name' => 'Chariot public — Consultation',     'category' => 'record',         'description' => 'Panier de consultation public', 'is_public' => true],
            ['name' => 'Chariot dossiers numériques',       'category' => 'digital_folder', 'description' => 'Dossiers numériques sélectionnés', 'is_public' => false],
            ['name' => 'Chariot documents numériques',      'category' => 'digital_document','description' => 'Documents numériques sélectionnés', 'is_public' => false],
        ];

        $createdDollies = [];
        foreach ($dollyDefs as $dd) {
            $createdDollies[] = Dolly::firstOrCreate(
                ['name' => $dd['name']],
                [
                    'description' => $dd['description'],
                    'category' => $dd['category'],
                    'is_public' => $dd['is_public'],
                    'created_by' => $user->id,
                    'owner_organisation_id' => $org->id,
                ]
            );
        }

        // --- 3. Dolly ↔ Entity pivot entries ---

        // Mail dolly (index 0) ↔ Mails
        $mails = Mail::take(3)->get();
        foreach ($mails as $mail) {
            DB::table('dolly_mails')->updateOrInsert(
                ['dolly_id' => $createdDollies[0]->id, 'mail_id' => $mail->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Record dolly (index 2) ↔ Records
        $records = RecordPhysical::take(3)->get();
        foreach ($records as $rec) {
            DB::table('dolly_records')->updateOrInsert(
                ['dolly_id' => $createdDollies[2]->id, 'record_id' => $rec->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Slip dolly (index 3) ↔ Slips
        $slips = Slip::take(2)->get();
        foreach ($slips as $slip) {
            DB::table('dolly_slips')->updateOrInsert(
                ['dolly_id' => $createdDollies[3]->id, 'slip_id' => $slip->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Communication dolly (index 4) ↔ Communications
        $coms = Communication::take(2)->get();
        foreach ($coms as $com) {
            DB::table('dolly_communications')->updateOrInsert(
                ['dolly_id' => $createdDollies[4]->id, 'communication_id' => $com->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Container dolly (index 5) ↔ Containers
        $containers = Container::take(3)->get();
        foreach ($containers as $ctn) {
            DB::table('dolly_containers')->updateOrInsert(
                ['dolly_id' => $createdDollies[5]->id, 'container_id' => $ctn->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('✅ Dollies: ' . count($typeDefs) . ' types, ' . count($createdDollies) . ' dollies with pivot entries seeded.');
    }
}
