<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that dashboard page loads successfully for authenticated user.
     */
    public function test_dashboard_loads_successfully(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertSee('Dashboard')
                    ->assertSee($user->name);
        });
    }

    /**
     * Test that dashboard displays stat cards.
     */
    public function test_dashboard_displays_stat_cards(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertSee('Folders')
                    ->assertSee('Documents')
                    ->assertSee('Periodicals');
        });
    }

    /**
     * Test that quick actions are displayed.
     */
    public function test_dashboard_displays_quick_actions(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertSee('Quick Actions')
                    ->assertSeeLink('New Folder')
                    ->assertSeeLink('Upload Document');
        });
    }

    /**
     * Test that navigation links work correctly.
     */
    public function test_navigation_links_work(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->clickLink('Folders')
                    ->assertPathIs('/folders')
                    ->back()
                    ->clickLink('Documents')
                    ->assertPathIs('/documents');
        });
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->assertPathIs('/login');
        });
    }

    /**
     * Test dark mode toggle works.
     */
    public function test_dark_mode_toggle_works(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->click('@dark-mode-toggle')
                    ->pause(500)
                    ->assertPresent('html.dark');
        });
    }

    /**
     * Test user dropdown menu.
     */
    public function test_user_dropdown_menu(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->click('@user-menu-button')
                    ->pause(300)
                    ->assertSee('Profile')
                    ->assertSee('Logout');
        });
    }
}
