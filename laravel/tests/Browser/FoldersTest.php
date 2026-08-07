<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FoldersTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test folders index page loads.
     */
    public function test_folders_index_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders')
                    ->assertSee('Folders')
                    ->assertSee('Tree View')
                    ->assertSee('List View');
        });
    }

    /**
     * Test tree view displays folders.
     */
    public function test_tree_view_displays_folders(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['name' => 'Test Folder', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $folder) {
            $browser->loginAs($user)
                    ->visit('/folders')
                    ->click('@tree-view-button')
                    ->pause(1000)
                    ->assertSee($folder->name);
        });
    }

    /**
     * Test create folder form displays.
     */
    public function test_create_folder_form_displays(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders/create')
                    ->assertSee('Create Folder')
                    ->assertInputValue('@folder-name', '')
                    ->assertSee('Parent Folder')
                    ->assertSee('Description');
        });
    }

    /**
     * Test can create new folder.
     */
    public function test_can_create_new_folder(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders/create')
                    ->type('name', 'New Test Folder')
                    ->type('description', 'This is a test folder')
                    ->press('Create Folder')
                    ->pause(500)
                    ->assertPathIs('/folders')
                    ->assertSee('Folder created successfully')
                    ->assertSee('New Test Folder');
        });
    }

    /**
     * Test folder validation errors.
     */
    public function test_folder_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders/create')
                    ->press('Create Folder')
                    ->pause(300)
                    ->assertSee('The name field is required');
        });
    }

    /**
     * Test can edit folder.
     */
    public function test_can_edit_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['name' => 'Original Name', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $folder) {
            $browser->loginAs($user)
                    ->visit("/folders/{$folder->id}/edit")
                    ->assertInputValue('name', 'Original Name')
                    ->clear('name')
                    ->type('name', 'Updated Name')
                    ->press('Update Folder')
                    ->pause(500)
                    ->assertPathIs('/folders/' . $folder->id)
                    ->assertSee('Folder updated successfully')
                    ->assertSee('Updated Name');
        });
    }

    /**
     * Test can delete folder.
     */
    public function test_can_delete_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['name' => 'To Delete', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $folder) {
            $browser->loginAs($user)
                    ->visit("/folders/{$folder->id}")
                    ->press('@delete-button')
                    ->pause(500)
                    ->whenAvailable('@confirm-dialog', function ($dialog) {
                        $dialog->press('Confirm');
                    })
                    ->pause(500)
                    ->assertPathIs('/folders')
                    ->assertSee('Folder deleted successfully')
                    ->assertDontSee('To Delete');
        });
    }

    /**
     * Test search functionality.
     */
    public function test_folder_search_works(): void
    {
        $user = User::factory()->create();
        Folder::factory()->create(['name' => 'Searchable Folder', 'user_id' => $user->id]);
        Folder::factory()->create(['name' => 'Another Folder', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders')
                    ->type('@search-input', 'Searchable')
                    ->pause(500)
                    ->assertSee('Searchable Folder')
                    ->assertDontSee('Another Folder');
        });
    }

    /**
     * Test pagination works.
     */
    public function test_pagination_works(): void
    {
        $user = User::factory()->create();
        Folder::factory()->count(25)->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/folders')
                    ->assertSee('Next')
                    ->clickLink('2')
                    ->pause(500)
                    ->assertQueryStringHas('page', '2');
        });
    }
}
