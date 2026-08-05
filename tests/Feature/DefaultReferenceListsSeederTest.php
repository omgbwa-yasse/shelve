<?php

namespace Tests\Feature;

use App\Models\ReferenceList;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DefaultReferenceListsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 2 — Dictionnaire des domaines par défaut + schéma lié.
 */
class DefaultReferenceListsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Seeder User',
            'email' => 'seeder@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    public function test_seeder_creates_9_default_domains(): void
    {
        $this->seed(DefaultReferenceListsSeeder::class);

        $codes = ReferenceList::pluck('code')->all();

        $this->assertContains('DOCUMENT_TYPES', $codes);

        foreach (ReferenceList::DEFAULT_SYSTEM_CODES as $code) {
            $this->assertContains($code, $codes, "Domaine manquant : {$code}");
        }

        $this->assertGreaterThanOrEqual(9, ReferenceList::count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DefaultReferenceListsSeeder::class);
        $count = ReferenceList::count();
        $this->seed(DefaultReferenceListsSeeder::class);

        $this->assertEquals($count, ReferenceList::count());
    }
}
