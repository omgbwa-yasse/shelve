<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\ToolOrganisationSeeder;
use App\Models\Organisation;

class OrganisationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_organisation_seeder_creates_exactly_four_main_organizations()
    {
        // Run the seeder
        $seeder = new ToolOrganisationSeeder();
        $seeder->run();

        // Assert we have exactly 4 main organizations (directions)
        // DG is the parent, DF, DRH, DSI are the 3 main directions
        $mainOrganizations = Organisation::whereIn('code', ['DG', 'DF', 'DRH', 'DSI'])->count();
        $this->assertEquals(4, $mainOrganizations);

        // Assert we have the expected organizations
        $this->assertDatabaseHas('organisations', ['code' => 'DG', 'name' => 'Direction Générale']);
        $this->assertDatabaseHas('organisations', ['code' => 'DF', 'name' => 'Direction des Finances']);
        $this->assertDatabaseHas('organisations', ['code' => 'DRH', 'name' => 'Direction des Ressources Humaines']);
        $this->assertDatabaseHas('organisations', ['code' => 'DSI', 'name' => 'Direction des Systèmes d\'Information']);

        // Assert we do NOT have the removed organizations
        $this->assertDatabaseMissing('organisations', ['code' => 'DCOM']);
        $this->assertDatabaseMissing('organisations', ['code' => 'DP']);
    }

    public function test_organization_hierarchy_is_correct()
    {
        // Run the seeder
        $seeder = new ToolOrganisationSeeder();
        $seeder->run();

        $directionGenerale = Organisation::where('code', 'DG')->first();
        $directionFinances = Organisation::where('code', 'DF')->first();
        $directionRH = Organisation::where('code', 'DRH')->first();
        $directionDSI = Organisation::where('code', 'DSI')->first();

        // Assert Direction Générale has no parent
        $this->assertNull($directionGenerale->parent_id);

        // Assert other directions have Direction Générale as parent
        $this->assertEquals($directionGenerale->id, $directionFinances->parent_id);
        $this->assertEquals($directionGenerale->id, $directionRH->parent_id);
        $this->assertEquals($directionGenerale->id, $directionDSI->parent_id);
    }

    public function test_essential_services_are_maintained()
    {
        // Run the seeder
        $seeder = new ToolOrganisationSeeder();
        $seeder->run();

        // Check that we still have important services under the remaining directions
        $this->assertDatabaseHas('organisations', ['code' => 'DF-COMPT']);
        $this->assertDatabaseHas('organisations', ['code' => 'DF-BUDG']);
        $this->assertDatabaseHas('organisations', ['code' => 'DRH-PAIE']);
        $this->assertDatabaseHas('organisations', ['code' => 'DRH-RECRU']);
        $this->assertDatabaseHas('organisations', ['code' => 'DSI-DEV']);
        $this->assertDatabaseHas('organisations', ['code' => 'DSI-INFRA']);
    }
}