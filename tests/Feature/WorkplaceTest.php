<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceTemplate;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkplaceInvitationMail;
use Tests\TestCase;

class WorkplaceTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $user;
    protected $organisation;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve Superadmin User
        $this->user = User::where('email', 'superadmin@example.com')->first();

        if (!$this->user) {
            // Create if not exists
            $this->organisation = Organisation::firstOrCreate(
                ['code' => 'TEST-ORG'],
                ['name' => 'Test Organisation']
            );

            $this->user = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('superadmin'),
                'current_organisation_id' => $this->organisation->id,
                'birthday' => '1990-01-01',
            ]);
        } else {
            // Load organisation
            if ($this->user->current_organisation_id) {
                $this->organisation = Organisation::find($this->user->current_organisation_id);
            }

            // Fallback if organisation not found or not set
            if (!$this->organisation) {
                 $this->organisation = Organisation::firstOrCreate(
                    ['code' => 'TEST-ORG'],
                    ['name' => 'Test Organisation']
                );
                $this->user->current_organisation_id = $this->organisation->id;
                $this->user->save();
            }
        }

        // Create Category if not exists
        $this->category = WorkplaceCategory::first();
        if (!$this->category) {
            $this->category = WorkplaceCategory::create([
                'name' => 'General',
                'code' => 'GEN-' . uniqid(),
                'is_active' => true,
            ]);
        }
    }

    public function test_user_can_create_workplace()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('workplaces.store'), [
            'name' => 'My Workplace',
            'category_id' => $this->category->id,
            'is_public' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workplaces', ['name' => 'My Workplace']);
        $this->assertDatabaseHas('workplace_members', [
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);
    }

    public function test_user_can_view_own_workplace()
    {
        $this->actingAs($this->user);

        $workplace = Workplace::create([
            'name' => 'My Workplace',
            'code' => 'WP-' . uniqid(),
            'category_id' => $this->category->id,
            'organisation_id' => $this->organisation->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $workplace->members()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $response = $this->get(route('workplaces.show', $workplace));
        $response->assertStatus(200);
    }

    public function test_superadmin_can_view_others_private_workplace()
    {
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'current_organisation_id' => $this->organisation->id,
            'birthday' => '1990-01-01',
        ]);

        $workplace = Workplace::create([
            'name' => 'Other Workplace',
            'code' => 'WP-' . uniqid(),
            'category_id' => $this->category->id,
            'organisation_id' => $this->organisation->id,
            'owner_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'is_public' => false,
        ]);

        $workplace->members()->create([
            'user_id' => $otherUser->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user); // Superadmin
        $response = $this->get(route('workplaces.show', $workplace));
        $response->assertStatus(200); // Superadmin should see it
    }

    public function test_invitation_sends_email()
    {
        Mail::fake();

        $this->actingAs($this->user);

        $workplace = Workplace::create([
            'name' => 'My Workplace',
            'code' => 'WP-' . uniqid(),
            'category_id' => $this->category->id,
            'organisation_id' => $this->organisation->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $workplace->members()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
            'can_invite' => true,
            'joined_at' => now(),
        ]);

        $response = $this->post(route('workplaces.members.store', $workplace), [
            'email' => 'invitee@example.com',
            'role' => 'editor',
        ]);

        $response->assertRedirect();

        Mail::assertSent(WorkplaceInvitationMail::class, function ($mail) {
            return $mail->hasTo('invitee@example.com');
        });

        $this->assertDatabaseHas('workplace_invitations', [
            'email' => 'invitee@example.com',
            'workplace_id' => $workplace->id,
        ]);
    }

    public function test_can_bookmark_workplace()
    {
        $this->actingAs($this->user);

        $workplace = Workplace::create([
            'name' => 'My Workplace',
            'code' => 'WP-' . uniqid(),
            'category_id' => $this->category->id,
            'organisation_id' => $this->organisation->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $workplace->members()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Add bookmark
        $response = $this->post(route('workplaces.bookmarks.store', $workplace), [
            'bookmarkable_type' => 'App\Models\Workplace', // Bookmarking the workplace itself for example, or a folder
            'bookmarkable_id' => $workplace->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workplace_bookmarks', [
            'user_id' => $this->user->id,
            'workplace_id' => $workplace->id,
        ]);

        // Remove bookmark (toggle)
        $response = $this->post(route('workplaces.bookmarks.store', $workplace), [
            'bookmarkable_type' => 'App\Models\Workplace',
            'bookmarkable_id' => $workplace->id,
        ]);

        $this->assertDatabaseMissing('workplace_bookmarks', [
            'user_id' => $this->user->id,
            'workplace_id' => $workplace->id,
        ]);
    }

    public function test_create_workplace_from_template()
    {
        $this->actingAs($this->user);

        $template = WorkplaceTemplate::create([
            'name' => 'Project Template',
            'code' => 'TPL-' . uniqid(),
            'created_by' => $this->user->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('workplaces.store'), [
            'name' => 'Templated Workplace',
            'category_id' => $this->category->id,
            'template_id' => $template->id,
        ]);

        if ($response->getSession()->has('errors')) {
            dump($response->getSession()->get('errors')->all());
        }

        $response->assertRedirect();
        $this->assertDatabaseHas('workplaces', ['name' => 'Templated Workplace']);

        $this->assertEquals(1, $template->fresh()->usage_count);
    }
}
