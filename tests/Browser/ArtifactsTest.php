<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Artifact;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ArtifactsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test artifacts index page loads with gallery view.
     */
    public function test_artifacts_index_loads_gallery_view(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts')
                    ->assertSee('Artifacts')
                    ->assertSee('Gallery View')
                    ->assertSee('List View')
                    ->assertPresent('.gallery-grid');
        });
    }

    /**
     * Test can toggle to list view.
     */
    public function test_can_toggle_to_list_view(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts')
                    ->click('@list-view-button')
                    ->pause(300)
                    ->assertPresent('.list-table')
                    ->assertDontSeeIn('.list-table', 'No artifacts found');
        });
    }

    /**
     * Test artifacts display in gallery.
     */
    public function test_artifacts_display_in_gallery(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create([
            'name' => 'Ancient Vase',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit('/artifacts')
                    ->assertSee('Ancient Vase')
                    ->assertPresent('.artifact-card');
        });
    }

    /**
     * Test create artifact form displays.
     */
    public function test_create_artifact_form_displays(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts/create')
                    ->assertSee('Create Artifact')
                    ->assertSee('Name')
                    ->assertSee('Description')
                    ->assertSee('Category')
                    ->assertSee('Date of Origin')
                    ->assertSee('Upload Images');
        });
    }

    /**
     * Test can create artifact.
     */
    public function test_can_create_artifact(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts/create')
                    ->type('name', 'Bronze Statue')
                    ->type('description', 'Ancient bronze statue from Rome')
                    ->select('category', 'Sculpture')
                    ->type('date_of_origin', '100 BC')
                    ->press('Create Artifact')
                    ->pause(500)
                    ->assertPathIs('/artifacts')
                    ->assertSee('Artifact created successfully')
                    ->assertSee('Bronze Statue');
        });
    }

    /**
     * Test artifact validation.
     */
    public function test_artifact_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts/create')
                    ->press('Create Artifact')
                    ->pause(300)
                    ->assertSee('The name field is required');
        });
    }

    /**
     * Test can view artifact details.
     */
    public function test_can_view_artifact_details(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create([
            'name' => 'Golden Crown',
            'description' => 'Royal crown from medieval period',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit("/artifacts/{$artifact->id}")
                    ->assertSee('Golden Crown')
                    ->assertSee('Royal crown from medieval period')
                    ->assertSee('Details')
                    ->assertSee('Images')
                    ->assertSee('Exhibitions')
                    ->assertSee('Loans');
        });
    }

    /**
     * Test exhibitions tab displays.
     */
    public function test_exhibitions_tab_displays(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit("/artifacts/{$artifact->id}")
                    ->click('@exhibitions-tab')
                    ->pause(500)
                    ->assertSee('Exhibitions')
                    ->assertSee('Add to Exhibition');
        });
    }

    /**
     * Test loans tab displays.
     */
    public function test_loans_tab_displays(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit("/artifacts/{$artifact->id}")
                    ->click('@loans-tab')
                    ->pause(500)
                    ->assertSee('Loans History')
                    ->assertSee('Create Loan');
        });
    }

    /**
     * Test can edit artifact.
     */
    public function test_can_edit_artifact(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create([
            'name' => 'Original Name',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit("/artifacts/{$artifact->id}/edit")
                    ->assertInputValue('name', 'Original Name')
                    ->clear('name')
                    ->type('name', 'Updated Name')
                    ->press('Update Artifact')
                    ->pause(500)
                    ->assertPathIs('/artifacts/' . $artifact->id)
                    ->assertSee('Artifact updated successfully')
                    ->assertSee('Updated Name');
        });
    }

    /**
     * Test image upload interface.
     */
    public function test_image_upload_interface(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts/create')
                    ->assertPresent('@image-upload-zone')
                    ->assertSee('Upload Images');
        });
    }

    /**
     * Test artifact search.
     */
    public function test_artifact_search(): void
    {
        $user = User::factory()->create();
        Artifact::factory()->create(['name' => 'Greek Pottery', 'user_id' => $user->id]);
        Artifact::factory()->create(['name' => 'Roman Coin', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/artifacts')
                    ->type('@search-input', 'Greek')
                    ->pause(500)
                    ->assertSee('Greek Pottery')
                    ->assertDontSee('Roman Coin');
        });
    }

    /**
     * Test can delete artifact.
     */
    public function test_can_delete_artifact(): void
    {
        $user = User::factory()->create();
        $artifact = Artifact::factory()->create([
            'name' => 'To Delete',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $artifact) {
            $browser->loginAs($user)
                    ->visit("/artifacts/{$artifact->id}")
                    ->press('@delete-button')
                    ->pause(500)
                    ->whenAvailable('@confirm-dialog', function ($dialog) {
                        $dialog->press('Confirm');
                    })
                    ->pause(500)
                    ->assertPathIs('/artifacts')
                    ->assertSee('Artifact deleted successfully');
        });
    }
}
