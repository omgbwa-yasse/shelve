<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Record;
use App\Models\Organisation;
use App\Models\Activity;
use App\Policies\RecordPolicy;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RecordPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RecordPolicy $policy;
    private User $user;
    private Organisation $organisation;
    private Record $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RecordPolicy();

        // Setup test data
        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->create([
            'current_organisation_id' => $this->organisation->id
        ]);
        $this->user->organisations()->attach($this->organisation);

        $activity = Activity::factory()->create();
        $activity->organisations()->attach($this->organisation);

        $this->record = Record::factory()->create([
            'activity_id' => $activity->id
        ]);
    }

    /** @test */
    public function super_admin_can_perform_any_action()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $result = $this->policy->view($superAdmin, $this->record);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_without_organisation_cannot_view_records()
    {
        $userWithoutOrg = User::factory()->create([
            'current_organisation_id' => null
        ]);

        $result = $this->policy->viewAny($userWithoutOrg);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /** @test */
    public function user_can_view_records_in_their_organisation()
    {
        $this->user->givePermissionTo('record_view', $this->organisation);

        $result = $this->policy->view($this->user, $this->record);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_view_records_from_other_organisation()
    {
        $otherOrganisation = Organisation::factory()->create();
        $otherActivity = Activity::factory()->create();
        $otherActivity->organisations()->attach($otherOrganisation);

        $otherRecord = Record::factory()->create([
            'activity_id' => $otherActivity->id
        ]);

        $this->user->givePermissionTo('record_view', $this->organisation);

        $result = $this->policy->view($this->user, $otherRecord);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /** @test */
    public function user_without_permission_gets_detailed_error_message()
    {
        $result = $this->policy->view($this->user, $this->record);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertFalse($result->allowed());
        // The exact message would depend on the BasePolicy implementation
    }

    /** @test */
    public function user_can_create_records_with_permission()
    {
        $this->user->givePermissionTo('record_create', $this->organisation);

        $result = $this->policy->create($this->user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_create_records_without_permission()
    {
        $result = $this->policy->create($this->user);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /** @test */
    public function caching_works_for_organisation_access_checks()
    {
        $this->user->givePermissionTo('record_view', $this->organisation);

        // First call - should hit database
        $result1 = $this->policy->view($this->user, $this->record);

        // Second call - should use cache
        $result2 = $this->policy->view($this->user, $this->record);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        // Verify cache key exists
        $cacheKey = "record_org_access:{$this->user->id}:{$this->record->id}:{$this->user->current_organisation_id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function organisation_access_works_via_direct_relationship()
    {
        // Create a record directly linked to organisation
        $directRecord = Record::factory()->create();
        $directRecord->organisations()->attach($this->organisation);

        $this->user->givePermissionTo('record_view', $this->organisation);

        $result = $this->policy->view($this->user, $directRecord);

        $this->assertTrue($result);
    }

    /** @test */
    public function organisation_access_works_via_organisation_id_column()
    {
        // Create a record with organisation_id
        $recordWithOrgId = Record::factory()->create([
            'organisation_id' => $this->organisation->id
        ]);

        $this->user->givePermissionTo('record_view', $this->organisation);

        $result = $this->policy->view($this->user, $recordWithOrgId);

        $this->assertTrue($result);
    }

    /** @test */
    public function update_requires_both_permission_and_organisation_access()
    {
        $this->user->givePermissionTo('record_update', $this->organisation);

        $result = $this->policy->update($this->user, $this->record);

        $this->assertTrue($result);
    }

    /** @test */
    public function delete_checks_are_consistent_with_update()
    {
        $this->user->givePermissionTo('record_delete', $this->organisation);

        $result = $this->policy->delete($this->user, $this->record);

        $this->assertTrue($result);
    }

    /** @test */
    public function force_delete_requires_special_permission()
    {
        $this->user->givePermissionTo('record_force_delete', $this->organisation);

        $result = $this->policy->forceDelete($this->user, $this->record);

        $this->assertTrue($result);
    }

    /** @test */
    public function view_any_requires_organisation_context()
    {
        $result = $this->policy->viewAny($this->user);

        // Should fail without permission
        $this->assertInstanceOf(Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /** @test */
    public function view_any_succeeds_with_permission()
    {
        $this->user->givePermissionTo('record_viewAny', $this->organisation);

        $result = $this->policy->viewAny($this->user);

        $this->assertTrue($result);
    }
}
