<?php

namespace Tests\Feature;

use App\Models\Communication;
use App\Models\CommunicationStatus;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommunicationStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        // Make sure we have statuses
        if (CommunicationStatus::count() == 0) {
            CommunicationStatus::insert([
                ['id' => 1, 'name' => 'Demande en cours', 'description' => 'Test'],
                ['id' => 2, 'name' => 'Validée', 'description' => 'Test'],
                ['id' => 3, 'name' => 'Rejetée', 'description' => 'Test'],
                ['id' => 4, 'name' => 'En consultation', 'description' => 'Test'],
                ['id' => 5, 'name' => 'Retournée', 'description' => 'Test'],
            ]);
        }
    }

    public function test_communication_status_transitions()
    {
        // Create test data
        $user = User::create([
            'name' => 'Test User',
            'surname' => 'Test',
            'birthday' => '1990-01-01',
            'email' => 'test' . time() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $organisation = Organisation::create([
            'code' => 'TEST-ORG',
            'name' => 'Test Organisation',
            'description' => 'Test Organisation for testing',
        ]);

        $communication = Communication::create([
            'code' => 'TEST-001',
            'name' => 'Test Communication',
            'content' => 'Test content',
            'user_id' => $user->id,
            'operator_id' => $user->id,
            'user_organisation_id' => $organisation->id,
            'operator_organisation_id' => $organisation->id,
            'return_date' => now()->addDays(30),
            'status_id' => 1, // Demande en cours
        ]);

        // Test status transitions
        $this->assertEquals(1, $communication->status_id);

        // Test validate transition
        $this->assertEquals(2, $communication->getNextStatusId('validate'));

        // Test transmit transition (only from validated)
        $communication->status_id = 2;
        $this->assertEquals(4, $communication->getNextStatusId('transmit'));

        // Test return transition (only from in consultation)
        $communication->status_id = 4;
        $this->assertEquals(5, $communication->getNextStatusId('return'));

        // Test cancel return transition
        $communication->status_id = 5;
        $this->assertEquals(4, $communication->getNextStatusId('cancel_return'));

        // Test business logic methods
        $communication->status_id = 5;
        $this->assertTrue($communication->isReturned());
        $this->assertFalse($communication->canBeEdited());

        $communication->status_id = 1;
        $this->assertFalse($communication->isReturned());
        $this->assertTrue($communication->canBeEdited());
    }
}
