<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailStatus;
use App\Models\MailType;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\MailSubject;
use App\Models\Mailbatch;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        MailPriority::insert([
            ['name' => 'Normale', 'duration' => 7],
            ['name' => 'Important', 'duration' => 3],
            ['name' => 'Urgent', 'duration' => 1],
        ]);

        MailTypology::insert([
            ['name' => 'Demande', 'description' => '', 'class_id' => 1],
            ['name' => 'Lettre', 'description' => '', 'class_id' => 1],
            ['name' => 'Décision', 'description' => '', 'class_id' => 1],
        ]);

        MailStatus::insert([
            ['name' => 'Brouillon'],
            ['name' => 'Traité'],
        ]);

        MailType::insert([
            ['name' => 'send'],
            ['name' => 'received'],
        ]);

        $dg = Organisation::create(['code' => 'DG', 'name' => 'Directeur général', 'description' => '']);
        $sg = Organisation::create(['code' => 'SG', 'name' => 'Secrétaire général', 'description' => '', 'parent_id' => $dg->id]);
        Organisation::create(['code' => 'RC', 'name' => 'Responsable du courier', 'description' => '', 'parent_id' => $sg->id]);
        $sg2 = Organisation::create(['code' => 'SG', 'name' => 'Secrétaire général', 'description' => 'Poste du Secrétaire général']);

        UserOrganisation::insert([
            ['user_id' => 1, 'organisation_id' => $dg->id, 'active' => true],
            ['user_id' => 1, 'organisation_id' => $sg->id, 'active' => false],
            ['user_id' => 1, 'organisation_id' => 3, 'active' => false],
        ]);

        Mailbatch::insert([
            ['code' => 'DG10', 'name' => 'Parapheur directeur général'],
            ['code' => 'DG09', 'name' => 'Parapheur Secrétaire général'],
        ]);

        Building::create(['name' => 'Archives de la BCGF', 'description' => '']);
        $floor = Floor::create(['name' => '2e étage', 'description' => '', 'building_id' => 1]);

        Room::create(['code' => 'Porte 201', 'name' => 'Archives financières', 'description' => '', 'floor_id' => $floor->id]);

        Shelf::create([
            'code' => 'E201-1',
            'observation' => '',
            'face' => 2,
            'ear' => 1,
            'shelf' => 6,
            'shelf_length' => 120,
            'room_id' => 1,
        ]);
    }
}
