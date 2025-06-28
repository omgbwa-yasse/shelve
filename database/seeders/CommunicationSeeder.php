<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        // Communications
        $communications = [];
        $communicationRecords = [];

        // Create 15 communications
        for ($i = 1; $i <= 15; $i++) {
            $operatorId = rand(5, 8); // Archivistes
            $userId = rand(15, 20); // Consulteurs

            $operatorOrgId = DB::table('users')->where('id', $operatorId)->value('current_organisation_id');
            $userOrgId = DB::table('users')->where('id', $userId)->value('current_organisation_id');

            $requestDate = Carbon::now()->subDays(rand(5, 30));
            $returnDate = $requestDate->copy()->addDays(15);
            $returnEffective = $i <= 10 ? $requestDate->copy()->addDays(rand(7, 14)) : null;

            // Map to enum values for communications
            if ($i <= 5) {
                $status = 'in_consultation';
            } elseif ($i <= 10) {
                $status = 'returned';
            } elseif ($i <= 12) {
                $status = 'approved';
            } elseif ($i <= 14) {
                $status = 'pending';
            } else {
                $status = 'rejected';
            }

            // Fix the code to be shorter
            $codeNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $code = 'C' . $codeNumber;

            $communications[] = [
                'id' => $i,
                'code' => $code,
                'name' => 'Demande de consultation ' . $i,
                'content' => 'Demande de consultation de documents d\'archives pour recherche administrative',
                'operator_id' => $operatorId,
                'operator_organisation_id' => $operatorOrgId,
                'user_id' => $userId,
                'user_organisation_id' => $userOrgId,
                'return_date' => $returnDate,
                'return_effective' => $returnEffective,
                'status' => $status,
                'created_at' => $requestDate,
                'updated_at' => $requestDate,
            ];

            // Add 1-3 records per communication
            $numRecords = rand(1, 3);
            for ($j = 1; $j <= $numRecords; $j++) {
                $recordId = rand(36, 50); // Using dossier records created in RecordSeeder

                $communicationRecords[] = [
                    'id' => ($i - 1) * 3 + $j,
                    'communication_id' => $i,
                    'record_id' => $recordId,
                    'content' => 'Document demandé pour consultation dans le cadre de recherches administratives',
                    'is_original' => $j == 1, // First record is original, others are copies
                    'return_date' => $returnDate,
                    'return_effective' => $returnEffective,
                    'operator_id' => $operatorId,
                    'created_at' => $requestDate,
                    'updated_at' => $requestDate,
                ];
            }
        }

        // Reservations
        $reservations = [];

        // Create 10 reservations
        for ($i = 1; $i <= 10; $i++) {
            $operatorId = rand(5, 8); // Archivistes
            $userId = rand(9, 14); // Producteurs

            $operatorOrgId = DB::table('users')->where('id', $operatorId)->value('current_organisation_id');
            $userOrgId = DB::table('users')->where('id', $userId)->value('current_organisation_id');

            $requestDate = Carbon::now()->subDays(rand(1, 15));

            // Map to enum values
            if ($i <= 6) {
                $status = 'approved';
            } elseif ($i <= 8) {
                $status = 'pending';
            } elseif ($i <= 9) {
                $status = 'rejected';
            } else {
                $status = 'cancelled';
            }

            // Fix the code to be shorter
            $codeNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $code = 'R' . $codeNumber;

            $reservations[] = [
                'id' => $i,
                'code' => $code,
                'name' => 'Réservation ' . $i,
                'content' => 'Réservation de documents pour consultation programmée',
                'operator_id' => $operatorId,
                'operator_organisation_id' => $operatorOrgId,
                'user_id' => $userId,
                'user_organisation_id' => $userOrgId,
                'status' => $status,
                'created_at' => $requestDate,
                'updated_at' => $requestDate,
            ];
        }

        DB::table('communications')->insert($communications);
        DB::table('communication_record')->insert($communicationRecords);
        DB::table('reservations')->insert($reservations);
    }
}
