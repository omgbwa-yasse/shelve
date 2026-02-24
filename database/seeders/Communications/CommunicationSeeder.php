<?php

namespace Database\Seeders\Communications;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Communication;
use App\Models\Reservation;
use App\Models\ReservationRecord;
use App\Models\RecordPhysical;
use App\Models\User;
use App\Models\Organisation;
use App\Enums\CommunicationStatus;
use App\Enums\ReservationStatus;

class CommunicationSeeder extends Seeder
{
    /**
     * Seed test data for the Communications module.
     * Creates communications in all statuses, linked records,
     * and reservations with reservation-records.
     * Idempotent: uses firstOrCreate/updateOrInsert.
     */
    public function run(): void
    {
        $this->command->info('📨 Seeding Communications module test data...');

        $users = User::take(4)->get();
        $orgs = Organisation::take(2)->get();
        $records = RecordPhysical::take(6)->get();

        if ($users->count() < 2 || $orgs->isEmpty() || $records->isEmpty()) {
            $this->command->warn('⚠️  Need users, organisations, and records. Run UserSeeder + RecordDataSeeder first.');
            return;
        }

        $operator = $users[0];
        $requester = $users[1] ?? $users[0];
        $org1 = $orgs[0];
        $org2 = $orgs[1] ?? $orgs[0];

        // --- 1. Communications (one per status) ---
        $comDefs = [
            ['code' => 'COM-000001', 'name' => 'Communication en attente — Dossier RH', 'status' => CommunicationStatus::PENDING, 'content' => 'Demande de consultation du dossier DUPONT pour vérification.', 'return_date' => now()->addDays(14)],
            ['code' => 'COM-000002', 'name' => 'Communication approuvée — Budget 2022', 'status' => CommunicationStatus::APPROVED, 'content' => 'Consultation approuvée pour le budget prévisionnel 2022.', 'return_date' => now()->addDays(30)],
            ['code' => 'COM-000003', 'name' => 'Communication rejetée — Confidentiel', 'status' => CommunicationStatus::REJECTED, 'content' => 'Dossier confidentiel — accès refusé.', 'return_date' => null],
            ['code' => 'COM-000004', 'name' => 'Communication en consultation', 'status' => CommunicationStatus::IN_CONSULTATION, 'content' => 'Documents en cours de consultation dans la salle de lecture.', 'return_date' => now()->addDays(7)],
            ['code' => 'COM-000005', 'name' => 'Communication retournée', 'status' => CommunicationStatus::RETURNED, 'content' => 'Documents retournés après consultation.', 'return_date' => now()->subDays(5), 'return_effective' => now()->subDay()],
        ];

        $createdComs = [];
        foreach ($comDefs as $def) {
            $com = Communication::firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'status' => $def['status'],
                    'content' => $def['content'],
                    'operator_id' => $operator->id,
                    'operator_organisation_id' => $org1->id,
                    'user_id' => $requester->id,
                    'user_organisation_id' => $org2->id,
                    'return_date' => $def['return_date'],
                    'return_effective' => $def['return_effective'] ?? null,
                ]
            );
            $createdComs[] = $com;
        }

        // --- 2. Communication–Record pivot entries ---
        foreach ($createdComs as $i => $com) {
            if ($records->count() > $i) {
                DB::table('communication_record')->updateOrInsert(
                    ['communication_id' => $com->id, 'record_id' => $records[$i]->id],
                    [
                        'content' => "Consultation de {$records[$i]->name}",
                        'is_original' => ($i % 2 === 0),
                        'return_date' => $com->return_date,
                        'return_effective' => $com->return_effective,
                        'operator_id' => $operator->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Extra: link multiple records to the approved communication
        if ($records->count() >= 4) {
            DB::table('communication_record')->updateOrInsert(
                ['communication_id' => $createdComs[1]->id, 'record_id' => $records[3]->id],
                [
                    'content' => 'Document complémentaire',
                    'is_original' => false,
                    'return_date' => $createdComs[1]->return_date,
                    'operator_id' => $operator->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // --- 3. Reservations (multiple statuses) ---
        $resDefs = [
            ['code' => 'RES-000001', 'name' => 'Réservation en attente', 'status' => ReservationStatus::PENDING, 'com_index' => 0],
            ['code' => 'RES-000002', 'name' => 'Réservation approuvée', 'status' => ReservationStatus::APPROVED, 'com_index' => 1],
            ['code' => 'RES-000003', 'name' => 'Réservation rejetée', 'status' => ReservationStatus::REJECTED, 'com_index' => null],
            ['code' => 'RES-000004', 'name' => 'Réservation annulée', 'status' => ReservationStatus::CANCELLED, 'com_index' => null],
            ['code' => 'RES-000005', 'name' => 'Réservation en cours', 'status' => ReservationStatus::IN_PROGRESS, 'com_index' => 3],
            ['code' => 'RES-000006', 'name' => 'Réservation terminée', 'status' => ReservationStatus::COMPLETED, 'com_index' => 4],
        ];

        $createdRes = [];
        foreach ($resDefs as $def) {
            $res = Reservation::firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'status' => $def['status'],
                    'content' => "Réservation de documents pour consultation.",
                    'operator_id' => $operator->id,
                    'operator_organisation_id' => $org1->id,
                    'user_id' => $requester->id,
                    'user_organisation_id' => $org2->id,
                    'communication_id' => $def['com_index'] !== null ? $createdComs[$def['com_index']]->id : null,
                    'return_date' => now()->addDays(14),
                    'return_effective' => $def['status'] === ReservationStatus::COMPLETED ? now()->subDay() : null,
                ]
            );
            $createdRes[] = $res;
        }

        // --- 4. Reservation–Record pivot entries ---
        foreach ($createdRes as $i => $res) {
            $recIndex = $i % $records->count();
            ReservationRecord::firstOrCreate(
                ['reservation_id' => $res->id, 'record_id' => $records[$recIndex]->id],
                [
                    'is_original' => ($i % 2 === 0),
                    'reservation_date' => now(),
                    'operator_id' => $operator->id,
                    'communication_id' => $res->communication_id,
                ]
            );
        }

        $this->command->info('✅ Communications: ' . count($createdComs) . ' communications, ' . count($createdRes) . ' reservations seeded.');
    }
}
