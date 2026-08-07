<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\RecordPeriodical;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PeriodicalsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test periodicals index page loads.
     */
    public function test_periodicals_index_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->assertSee('Periodicals')
                    ->assertSee('Browse Journals & Magazines')
                    ->assertSee('Search');
        });
    }

    /**
     * Test periodicals display in list.
     */
    public function test_periodicals_display_in_list(): void
    {
        $user = User::factory()->create();
        $periodical = RecordPeriodical::factory()->create([
            'title' => 'Science Monthly',
            'issn' => '1234-5678'
        ]);

        $this->browse(function (Browser $browser) use ($user, $periodical) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->assertSee('Science Monthly')
                    ->assertSee('1234-5678');
        });
    }

    /**
     * Test can view periodical details.
     */
    public function test_can_view_periodical_details(): void
    {
        $user = User::factory()->create();
        $periodical = RecordPeriodical::factory()->create([
            'title' => 'History Quarterly',
            'issn' => '9876-5432',
            'publisher' => 'Academic Press'
        ]);

        $this->browse(function (Browser $browser) use ($user, $periodical) {
            $browser->loginAs($user)
                    ->visit("/periodicals/{$periodical->id}")
                    ->assertSee('History Quarterly')
                    ->assertSee('9876-5432')
                    ->assertSee('Academic Press')
                    ->assertSee('Issues')
                    ->assertSee('Articles');
        });
    }

    /**
     * Test issues tab displays.
     */
    public function test_issues_tab_displays(): void
    {
        $user = User::factory()->create();
        $periodical = RecordPeriodical::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $periodical) {
            $browser->loginAs($user)
                    ->visit("/periodicals/{$periodical->id}")
                    ->click('@issues-tab')
                    ->pause(500)
                    ->assertSee('Issues')
                    ->assertSee('Volume')
                    ->assertSee('Publication Date');
        });
    }

    /**
     * Test articles search works.
     */
    public function test_articles_search_works(): void
    {
        $user = User::factory()->create();
        $periodical = RecordPeriodical::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $periodical) {
            $browser->loginAs($user)
                    ->visit("/periodicals/{$periodical->id}")
                    ->click('@articles-tab')
                    ->pause(500)
                    ->type('@article-search', 'climate change')
                    ->press('Search')
                    ->pause(500)
                    ->assertPathIs("/periodicals/{$periodical->id}/articles");
        });
    }

    /**
     * Test periodical search.
     */
    public function test_periodical_search(): void
    {
        $user = User::factory()->create();
        RecordPeriodical::factory()->create(['title' => 'Nature Magazine']);
        RecordPeriodical::factory()->create(['title' => 'Science Journal']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->type('@search-input', 'Nature')
                    ->pause(500)
                    ->assertSee('Nature Magazine')
                    ->assertDontSee('Science Journal');
        });
    }

    /**
     * Test ISSN display and formatting.
     */
    public function test_issn_display_and_formatting(): void
    {
        $user = User::factory()->create();
        $periodical = RecordPeriodical::factory()->create([
            'title' => 'Tech Review',
            'issn' => '1111-2222'
        ]);

        $this->browse(function (Browser $browser) use ($user, $periodical) {
            $browser->loginAs($user)
                    ->visit("/periodicals/{$periodical->id}")
                    ->assertSee('ISSN: 1111-2222');
        });
    }

    /**
     * Test pagination works.
     */
    public function test_pagination_works(): void
    {
        $user = User::factory()->create();
        RecordPeriodical::factory()->count(25)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->assertSee('Next')
                    ->clickLink('2')
                    ->pause(500)
                    ->assertQueryStringHas('page', '2');
        });
    }

    /**
     * Test filter by publisher.
     */
    public function test_filter_by_publisher(): void
    {
        $user = User::factory()->create();
        RecordPeriodical::factory()->create([
            'title' => 'Journal A',
            'publisher' => 'Oxford Press'
        ]);
        RecordPeriodical::factory()->create([
            'title' => 'Journal B',
            'publisher' => 'Cambridge Press'
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->select('@publisher-filter', 'Oxford Press')
                    ->pause(500)
                    ->assertSee('Journal A')
                    ->assertDontSee('Journal B');
        });
    }

    /**
     * Test empty state displays when no periodicals.
     */
    public function test_empty_state_displays(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/periodicals')
                    ->assertSee('No periodicals found');
        });
    }
}
