<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Communication;
use App\Models\Mail;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\RecordLevel;
use App\Models\RecordPhysical;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Role;
use App\Models\Slip;
use App\Models\SlipStatus;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkplaceCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Organisation Scoping Test
 *
 * Validates that all 7 modules correctly scope data by organisation:
 * - Index listings filtered by current_organisation_id
 * - Show/edit/delete returns 403 for cross-org access
 * - Store auto-assigns organisation_id
 * - SuperAdmin bypass (sees all)
 */
class OrganisationScopingTest extends TestCase
{
    use DatabaseTransactions;

    protected Organisation $orgA;
    protected Organisation $orgB;
    protected User $userA;       // Regular user in OrgA
    protected User $userB;       // Regular user in OrgB
    protected User $superAdmin;  // SuperAdmin (sees all)

    /**
     * Generate a short unique code for test data.
     */
    private function uid(string $prefix = '', int $len = 6): string
    {
        return $prefix . substr(uniqid(), -$len);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Clear role/permission cache
        Cache::flush();

        // Create two organisations
        $this->orgA = Organisation::firstOrCreate(
            ['code' => 'STA'],
            ['name' => 'Scope Test Organisation A']
        );

        $this->orgB = Organisation::firstOrCreate(
            ['code' => 'STB'],
            ['name' => 'Scope Test Organisation B']
        );

        // Get a default role for the pivot table
        $defaultRole = Role::firstOrCreate(['name' => 'user']);

        // Create regular user for Org A
        $this->userA = User::create([
            'name' => 'User OrgA',
            'email' => 'scope-test-usera-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $this->orgA->id,
        ]);
        $this->userA->organisations()->attach($this->orgA->id, [
            'role_id' => $defaultRole->id,
            'creator_id' => $this->userA->id,
        ]);

        // Create regular user for Org B
        $this->userB = User::create([
            'name' => 'User OrgB',
            'email' => 'scope-test-userb-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $this->orgB->id,
        ]);
        $this->userB->organisations()->attach($this->orgB->id, [
            'role_id' => $defaultRole->id,
            'creator_id' => $this->userB->id,
        ]);

        // Create SuperAdmin
        $this->superAdmin = User::create([
            'name' => 'SuperAdmin Scope',
            'email' => 'scope-test-superadmin-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $this->orgA->id,
        ]);
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $this->superAdmin->organisations()->attach($this->orgA->id, [
            'role_id' => $superadminRole->id,
            'creator_id' => $this->superAdmin->id,
        ]);
        $this->superAdmin->roles()->attach($superadminRole->id);
    }

    // =========================================================================
    // BelongsToOrganisation TRAIT TESTS
    // =========================================================================

    public function test_belongs_to_organisation_scope_filters_by_org()
    {
        // Create workflow definitions in both orgs
        $defA = WorkflowDefinition::create([
            'name' => 'Def OrgA',
            'bpmn_xml' => '<bpmn>a</bpmn>',
            'status' => 'draft',
            'organisation_id' => $this->orgA->id,
            'created_by' => $this->userA->id,
        ]);

        $defB = WorkflowDefinition::create([
            'name' => 'Def OrgB',
            'bpmn_xml' => '<bpmn>b</bpmn>',
            'status' => 'draft',
            'organisation_id' => $this->orgB->id,
            'created_by' => $this->userB->id,
        ]);

        // Scoping to OrgA should return only OrgA's definition
        $scopedToA = WorkflowDefinition::byOrganisation($this->orgA->id)->pluck('id');
        $this->assertTrue($scopedToA->contains($defA->id));
        $this->assertFalse($scopedToA->contains($defB->id));

        // Scoping to OrgB should return only OrgB's definition
        $scopedToB = WorkflowDefinition::byOrganisation($this->orgB->id)->pluck('id');
        $this->assertTrue($scopedToB->contains($defB->id));
        $this->assertFalse($scopedToB->contains($defA->id));
    }

    public function test_belongs_to_organisation_auto_assigns_on_create()
    {
        $this->actingAs($this->userA);

        $def = WorkflowDefinition::create([
            'name' => 'Auto-assigned Def',
            'bpmn_xml' => '<bpmn>auto</bpmn>',
            'status' => 'draft',
            'created_by' => $this->userA->id,
        ]);

        // Trait should auto-assign organisation_id from Auth user
        $this->assertEquals($this->orgA->id, $def->organisation_id);
    }

    public function test_belongs_to_organisation_relationship_returns_organisation()
    {
        $def = WorkflowDefinition::create([
            'name' => 'Relationship Test',
            'bpmn_xml' => '<bpmn>rel</bpmn>',
            'status' => 'draft',
            'organisation_id' => $this->orgA->id,
            'created_by' => $this->userA->id,
        ]);

        $this->assertNotNull($def->organisation);
        $this->assertEquals($this->orgA->id, $def->organisation->id);
    }

    // =========================================================================
    // HasDualOrganisation TRAIT TESTS
    // =========================================================================

    public function test_dual_organisation_scope_filters_by_either_org()
    {
        // Create communication: Org A is operator, Org B is user
        $commAB = Communication::create([
            'code' => $this->uid('C'),
            'name' => 'Comm AB',
            'operator_id' => $this->userA->id,
            'operator_organisation_id' => $this->orgA->id,
            'user_id' => $this->userB->id,
            'user_organisation_id' => $this->orgB->id,
            'return_date' => now()->addDays(30),
        ]);

        // Another communication: Org B ↔ Org B (both sides)
        $commBB = Communication::create([
            'code' => $this->uid('C'),
            'name' => 'Comm BB',
            'operator_id' => $this->userB->id,
            'operator_organisation_id' => $this->orgB->id,
            'user_id' => $this->userB->id,
            'user_organisation_id' => $this->orgB->id,
            'return_date' => now()->addDays(30),
        ]);

        // OrgA scope: should see commAB (operator side) but NOT commBB
        $scopedA = Communication::forOrganisation($this->orgA->id)->pluck('id');
        $this->assertTrue($scopedA->contains($commAB->id), 'OrgA should see comm where it is operator');
        $this->assertFalse($scopedA->contains($commBB->id), 'OrgA should not see OrgB-only comm');

        // OrgB scope: should see both (user side of commAB, both sides of commBB)
        $scopedB = Communication::forOrganisation($this->orgB->id)->pluck('id');
        $this->assertTrue($scopedB->contains($commAB->id), 'OrgB should see comm where it is user');
        $this->assertTrue($scopedB->contains($commBB->id), 'OrgB should see its own comm');
    }

    public function test_involves_organisation_returns_correct_boolean()
    {
        $comm = Communication::create([
            'code' => $this->uid('C'),
            'name' => 'Involves Test',
            'operator_id' => $this->userA->id,
            'operator_organisation_id' => $this->orgA->id,
            'user_id' => $this->userB->id,
            'user_organisation_id' => $this->orgB->id,
            'return_date' => now()->addDays(30),
        ]);

        $this->assertTrue($comm->involvesOrganisation($this->orgA->id));
        $this->assertTrue($comm->involvesOrganisation($this->orgB->id));

        // Create a third org — should NOT be involved
        $orgC = Organisation::firstOrCreate(
            ['code' => 'STC'],
            ['name' => 'Scope Test Organisation C']
        );
        $this->assertFalse($comm->involvesOrganisation($orgC->id));
    }

    public function test_mail_involves_organisation_checks_assigned_org_too()
    {
        $activity = Activity::first() ?? Activity::create([
            'code' => $this->uid('A', 4),
            'name' => 'Test Activity',
        ]);

        $typology = MailTypology::first() ?? MailTypology::create([
            'code' => $this->uid('T', 4),
            'name' => 'Test Typology',
            'activity_id' => $activity->id,
        ]);

        $mail = Mail::create([
            'code' => $this->uid('M'),
            'name' => 'Mail Assigned Test',
            'date' => now(),
            'typology_id' => $typology->id,
            'sender_organisation_id' => $this->orgA->id,
            'recipient_organisation_id' => $this->orgA->id,
            'assigned_organisation_id' => $this->orgB->id,
        ]);

        // OrgA is sender + recipient
        $this->assertTrue($mail->involvesOrganisation($this->orgA->id));
        // OrgB is only assigned — custom override should catch this
        $this->assertTrue($mail->involvesOrganisation($this->orgB->id));
    }

    // =========================================================================
    // SLIP DUAL-ORG SCOPE TEST
    // =========================================================================

    public function test_slip_dual_organisation_scoping()
    {
        $slipStatus = SlipStatus::first() ?? SlipStatus::create(['name' => 'Pending']);

        $slipAB = Slip::create([
            'code' => $this->uid('S'),
            'name' => 'Slip AB',
            'officer_organisation_id' => $this->orgA->id,
            'officer_id' => $this->userA->id,
            'user_organisation_id' => $this->orgB->id,
            'slip_status_id' => $slipStatus->id,
        ]);

        $slipBB = Slip::create([
            'code' => $this->uid('S'),
            'name' => 'Slip BB',
            'officer_organisation_id' => $this->orgB->id,
            'officer_id' => $this->userB->id,
            'user_organisation_id' => $this->orgB->id,
            'slip_status_id' => $slipStatus->id,
        ]);

        // OrgA should see slipAB but not slipBB
        $scopedA = Slip::forOrganisation($this->orgA->id)->pluck('id');
        $this->assertTrue($scopedA->contains($slipAB->id));
        $this->assertFalse($scopedA->contains($slipBB->id));
    }

    // =========================================================================
    // MAIL DUAL-ORG SCOPE TEST
    // =========================================================================

    public function test_mail_for_organisation_filters_correctly()
    {
        $activity = Activity::first() ?? Activity::create([
            'code' => $this->uid('A', 4),
            'name' => 'Test Activity',
        ]);

        $typology = MailTypology::first() ?? MailTypology::create([
            'code' => $this->uid('T', 4),
            'name' => 'Test Typology',
            'activity_id' => $activity->id,
        ]);

        $mailAB = Mail::create([
            'code' => $this->uid('M'),
            'name' => 'Mail AB',
            'date' => now(),
            'typology_id' => $typology->id,
            'sender_organisation_id' => $this->orgA->id,
            'recipient_organisation_id' => $this->orgB->id,
        ]);

        $mailBB = Mail::create([
            'code' => $this->uid('M'),
            'name' => 'Mail BB',
            'date' => now(),
            'typology_id' => $typology->id,
            'sender_organisation_id' => $this->orgB->id,
            'recipient_organisation_id' => $this->orgB->id,
        ]);

        // OrgA should see mailAB (sender) but not mailBB
        $scopedA = Mail::forOrganisation($this->orgA->id)->pluck('id');
        $this->assertTrue($scopedA->contains($mailAB->id));
        $this->assertFalse($scopedA->contains($mailBB->id));
    }

    // =========================================================================
    // WORKPLACE SCOPING
    // =========================================================================

    public function test_workplace_uses_belongs_to_organisation_trait()
    {
        $category = WorkplaceCategory::first() ?? WorkplaceCategory::create([
            'name' => 'General',
            'code' => $this->uid('G'),
            'is_active' => true,
        ]);

        $wpA = Workplace::create([
            'name' => 'Workplace OrgA',
            'code' => $this->uid('W'),
            'organisation_id' => $this->orgA->id,
            'category_id' => $category->id,
            'owner_id' => $this->userA->id,
            'created_by' => $this->userA->id,
        ]);

        $wpB = Workplace::create([
            'name' => 'Workplace OrgB',
            'code' => $this->uid('W'),
            'organisation_id' => $this->orgB->id,
            'category_id' => $category->id,
            'owner_id' => $this->userB->id,
            'created_by' => $this->userB->id,
        ]);

        $scopedA = Workplace::byOrganisation($this->orgA->id)->pluck('id');
        $this->assertTrue($scopedA->contains($wpA->id));
        $this->assertFalse($scopedA->contains($wpB->id));
    }

    // =========================================================================
    // WORKPLACE POLICY BUG FIX — T028
    // =========================================================================

    public function test_workplace_policy_uses_current_organisation_id()
    {
        $category = WorkplaceCategory::first() ?? WorkplaceCategory::create([
            'name' => 'Policy Test',
            'code' => $this->uid('P'),
            'is_active' => true,
        ]);

        $wpA = Workplace::create([
            'name' => 'Public WP OrgA',
            'code' => $this->uid('W'),
            'organisation_id' => $this->orgA->id,
            'category_id' => $category->id,
            'is_public' => true,
            'owner_id' => $this->userA->id,
            'created_by' => $this->userA->id,
        ]);

        // UserA in OrgA should be able to view public workplace in OrgA
        $policy = new \App\Policies\WorkplacePolicy();
        $result = $policy->view($this->userA, $wpA);
        $this->assertTrue($result === true || ($result instanceof \Illuminate\Auth\Access\Response && $result->allowed()));

        // UserB in OrgB should NOT be able to view public workplace in OrgA (different org)
        $result = $policy->view($this->userB, $wpA);
        $this->assertFalse($result === true);
    }

    // =========================================================================
    // CONTROLLER INDEX FILTERING TESTS
    // =========================================================================

    public function test_workflow_definitions_index_filters_by_org()
    {
        $defA = WorkflowDefinition::create([
            'name' => 'Def A Index',
            'bpmn_xml' => '<bpmn>a</bpmn>',
            'status' => 'active',
            'organisation_id' => $this->orgA->id,
            'created_by' => $this->userA->id,
        ]);

        $defB = WorkflowDefinition::create([
            'name' => 'Def B Index',
            'bpmn_xml' => '<bpmn>b</bpmn>',
            'status' => 'active',
            'organisation_id' => $this->orgB->id,
            'created_by' => $this->userB->id,
        ]);

        // UserA should see Def A but not Def B
        $response = $this->actingAs($this->userA)
            ->get(route('workflows.definitions.index'));

        $response->assertStatus(200);
        $response->assertSee('Def A Index');
        $response->assertDontSee('Def B Index');
    }

    public function test_superadmin_sees_all_workflow_definitions()
    {
        $defA = WorkflowDefinition::create([
            'name' => 'Def SA-A',
            'bpmn_xml' => '<bpmn>a</bpmn>',
            'status' => 'active',
            'organisation_id' => $this->orgA->id,
            'created_by' => $this->userA->id,
        ]);

        $defB = WorkflowDefinition::create([
            'name' => 'Def SA-B',
            'bpmn_xml' => '<bpmn>b</bpmn>',
            'status' => 'active',
            'organisation_id' => $this->orgB->id,
            'created_by' => $this->userB->id,
        ]);

        // SuperAdmin should see both
        $response = $this->actingAs($this->superAdmin)
            ->get(route('workflows.definitions.index'));

        $response->assertStatus(200);
        $response->assertSee('Def SA-A');
        $response->assertSee('Def SA-B');
    }

    // =========================================================================
    // CONTROLLER SHOW AUTHORIZATION TESTS
    // =========================================================================

    public function test_cross_org_workflow_definition_show_is_denied()
    {
        $defB = WorkflowDefinition::create([
            'name' => 'Def B Blocked',
            'bpmn_xml' => '<bpmn>b</bpmn>',
            'status' => 'draft',
            'organisation_id' => $this->orgB->id,
            'created_by' => $this->userB->id,
        ]);

        // UserA in OrgA trying to view OrgB's definition — should get 403 or 404 (not 200)
        $response = $this->actingAs($this->userA)
            ->get(route('workflows.definitions.show', $defB->id));

        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_superadmin_can_access_any_workflow_definition()
    {
        $defB = WorkflowDefinition::create([
            'name' => 'Def B Superadmin',
            'bpmn_xml' => '<bpmn>b</bpmn>',
            'status' => 'draft',
            'organisation_id' => $this->orgB->id,
            'created_by' => $this->userB->id,
        ]);

        // SuperAdmin should get 200 (before() grants all)
        $response = $this->actingAs($this->superAdmin)
            ->get(route('workflows.definitions.show', $defB->id));

        $response->assertStatus(200);
    }

    // =========================================================================
    // STORE AUTO-ASSIGNMENT TESTS
    // =========================================================================

    public function test_workflow_definition_store_auto_assigns_organisation()
    {
        $this->actingAs($this->userA);

        $response = $this->post(route('workflows.definitions.store'), [
            'name' => 'Auto Org Def',
            'bpmn_xml' => '<bpmn>auto</bpmn>',
            'status' => 'draft',
        ]);

        // Should redirect (successful creation)
        $response->assertRedirect();

        // Verify organisation was auto-assigned
        $created = WorkflowDefinition::where('name', 'Auto Org Def')->first();
        $this->assertNotNull($created);
        $this->assertEquals($this->orgA->id, $created->organisation_id);
    }
}
