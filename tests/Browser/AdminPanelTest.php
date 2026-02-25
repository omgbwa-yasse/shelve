<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminPanelTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin dashboard loads for admin users.
     */
    public function test_admin_dashboard_loads_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin')
                    ->assertSee('Admin Dashboard')
                    ->assertSee('System Statistics')
                    ->assertSee('Users')
                    ->assertSee('Settings');
        });
    }

    /**
     * Test non-admin users cannot access admin panel.
     */
    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/admin')
                    ->assertPathIsNot('/admin')
                    ->assertSee('Unauthorized');
        });
    }

    /**
     * Test admin statistics display correctly.
     */
    public function test_admin_statistics_display(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(10)->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin')
                    ->assertSee('Total Users')
                    ->assertSee('Total Documents')
                    ->assertSee('Total Folders');
        });
    }

    /**
     * Test user management page loads.
     */
    public function test_user_management_page_loads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->assertSee('User Management')
                    ->assertSee('Name')
                    ->assertSee('Email')
                    ->assertSee('Role')
                    ->assertSee('Actions');
        });
    }

    /**
     * Test users list displays.
     */
    public function test_users_list_displays(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $this->browse(function (Browser $browser) use ($admin, $user) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->assertSee('Test User')
                    ->assertSee('test@example.com');
        });
    }

    /**
     * Test can search users.
     */
    public function test_can_search_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->type('@search-input', 'John')
                    ->pause(500)
                    ->assertSee('John Doe')
                    ->assertDontSee('Jane Smith');
        });
    }

    /**
     * Test can filter users by role.
     */
    public function test_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Admin User', 'role' => 'admin']);
        User::factory()->create(['name' => 'Regular User', 'role' => 'user']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->select('@role-filter', 'admin')
                    ->pause(500)
                    ->assertSee('Admin User')
                    ->assertDontSee('Regular User');
        });
    }

    /**
     * Test settings page loads.
     */
    public function test_settings_page_loads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/settings')
                    ->assertSee('System Settings')
                    ->assertSee('General')
                    ->assertSee('Security')
                    ->assertSee('Email');
        });
    }

    /**
     * Test can update settings.
     */
    public function test_can_update_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/settings')
                    ->type('app_name', 'Shelve Archive')
                    ->type('app_email', 'admin@shelve.test')
                    ->press('Save Settings')
                    ->pause(500)
                    ->assertSee('Settings updated successfully');
        });
    }

    /**
     * Test logs page loads.
     */
    public function test_logs_page_loads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/logs')
                    ->assertSee('System Logs')
                    ->assertSee('Activity')
                    ->assertSee('Errors')
                    ->assertSee('Security');
        });
    }

    /**
     * Test logs pagination works.
     */
    public function test_logs_pagination_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/logs')
                    ->assertSee('Showing')
                    ->assertSee('entries');
        });
    }

    /**
     * Test can filter logs by type.
     */
    public function test_can_filter_logs_by_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/logs')
                    ->select('@log-type-filter', 'error')
                    ->pause(500)
                    ->assertQueryStringHas('type', 'error');
        });
    }

    /**
     * Test admin navigation menu displays.
     */
    public function test_admin_navigation_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin')
                    ->assertSeeLink('Dashboard')
                    ->assertSeeLink('Users')
                    ->assertSeeLink('Settings')
                    ->assertSeeLink('Logs');
        });
    }

    /**
     * Test quick stats cards display.
     */
    public function test_quick_stats_cards_display(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin')
                    ->assertPresent('.stat-card')
                    ->assertSee('Total')
                    ->assertSee('Active');
        });
    }
}
