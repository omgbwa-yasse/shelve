<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Record;
use App\Models\RecordComment;
use App\Models\RecordShare;
use App\Models\RecordType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTestRecords;
use Tests\TestCase;

/**
 * Étape 8 — Collaboration sur les notices : partage, favoris, commentaires,
 * raccourcis.
 */
class RecordCollaborationTest extends TestCase
{
    use RefreshDatabase, WithTestRecords;

    protected User $owner;
    protected User $collaborator;
    protected Record $record;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->owner = User::create([
            'name' => 'Owner ' . self::$counter,
            'email' => 'owner-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->collaborator = User::create([
            'name' => 'Collab ' . self::$counter,
            'email' => 'collab-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $this->owner->roles()->attach($role->id);
        $this->collaborator->roles()->attach($role->id);

        $type = RecordType::create(['code' => 'RT-COLL-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);
        $this->record = Record::create([
            'code' => 'RC-COLL-' . self::$counter,
            'name' => 'Notice collaborative',
            'type_id' => $type->id,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->owner->id,
        ]);
    }

    public function test_share_record_with_expiration(): void
    {
        $response = $this->actingAs($this->owner)->post(route('records.shares.store', $this->record), [
            'user_id' => $this->collaborator->id,
            'permission' => 'view',
            'expires_at' => now()->addDays(7)->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect(route('records.shares', $this->record));

        $share = RecordShare::first();

        $this->assertNotNull($share);
        $this->assertEquals($this->collaborator->id, $share->user_id);
        $this->assertFalse($share->isExpired());
        $this->assertTrue($this->record->isSharedWith($this->collaborator->id));
    }

    public function test_share_requires_user_or_role(): void
    {
        $response = $this->actingAs($this->owner)->post(route('records.shares.store', $this->record), [
            'user_id' => '',
            'permission' => 'view',
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    public function test_toggle_favorite(): void
    {
        $response = $this->actingAs($this->owner)->post(route('records.favorite.toggle', $this->record));
        $response->assertJson(['favorite' => true]);
        $this->assertEquals(1, Favorite::count());

        $response = $this->actingAs($this->owner)->post(route('records.favorite.toggle', $this->record));
        $response->assertJson(['favorite' => false]);
        $this->assertEquals(0, Favorite::count());
    }

    public function test_comment_can_only_be_edited_by_author(): void
    {
        $comment = RecordComment::create([
            'record_id' => $this->record->id,
            'user_id' => $this->owner->id,
            'content' => 'Premier commentaire',
        ]);

        // Un autre utilisateur ne peut pas modifier
        $response = $this->actingAs($this->collaborator)->put(route('records.comments.update', [$this->record, $comment]), [
            'content' => 'Piraté',
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404], true));

        // L'auteur peut modifier
        $response = $this->actingAs($this->owner)->put(route('records.comments.update', [$this->record, $comment]), [
            'content' => 'Corrigé',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Corrigé', $comment->fresh()->content);
    }

    public function test_store_shortcut(): void
    {
        $response = $this->actingAs($this->owner)->post(route('records.shortcuts.store', $this->record), [
            'label' => 'Accès direct',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('record_shortcuts', ['record_id' => $this->record->id, 'user_id' => $this->owner->id]);
    }
}
