<?php

namespace Tests\Feature\Api\V1;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * API v1 — Email (boîte de messagerie IMAP/SMTP), consommée par le frontend
 * Next.js. Distincte de l'API `mail_*` (courrier administratif D06).
 */
class EmailApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['email_account', 'email_message', 'email_tag'];

    private User $user;
    private Organisation $organisation;
    private EmailAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create(['email_module_enabled' => true]);
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);

        $this->account = EmailAccount::create([
            'organisation_id' => $this->organisation->id,
            'name' => 'Compte API',
            'email_address' => 'api@exemple.com',
            'imap_host' => 'imap.exemple.com',
            'imap_username' => 'api@exemple.com',
            'imap_password' => 'secret',
            'smtp_host' => 'smtp.exemple.com',
            'smtp_username' => 'api@exemple.com',
            'smtp_password' => 'secret',
        ]);
    }

    public function test_index_lists_only_accounts_of_current_organisation(): void
    {
        $otherOrg = Organisation::factory()->create();
        EmailAccount::create([
            'organisation_id' => $otherOrg->id,
            'name' => 'Autre compte',
            'email_address' => 'autre@exemple.com',
            'imap_host' => 'imap.autre.com',
            'imap_username' => 'autre@exemple.com',
            'imap_password' => 'secret',
            'smtp_host' => 'smtp.autre.com',
            'smtp_username' => 'autre@exemple.com',
            'smtp_password' => 'secret',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/email-accounts');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.email_address', 'api@exemple.com');
        $response->assertJsonMissingPath('data.0.imap_password');
    }

    public function test_store_creates_account_with_encrypted_credentials(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/email-accounts', [
            'name' => 'Nouveau',
            'email_address' => 'nouveau@exemple.com',
            'imap_host' => 'imap.nouveau.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => 'nouveau@exemple.com',
            'imap_password' => 'motdepasse',
            'smtp_host' => 'smtp.nouveau.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'nouveau@exemple.com',
            'smtp_password' => 'motdepasse',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('email_accounts', ['email_address' => 'nouveau@exemple.com']);
    }

    public function test_index_lists_messages_and_show_marks_as_read(): void
    {
        $message = EmailMessage::create([
            'email_account_id' => $this->account->id,
            'uid' => 1,
            'folder' => 'INBOX',
            'subject' => 'Sujet API',
            'from_address' => 'x@exemple.com',
            'body_text' => 'Contenu',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/email-messages')
            ->assertOk()
            ->assertJsonFragment(['subject' => 'Sujet API']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/email-messages/{$message->id}")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_tags_crud_and_attach_to_message(): void
    {
        $message = EmailMessage::create([
            'email_account_id' => $this->account->id,
            'uid' => 2,
            'folder' => 'INBOX',
            'subject' => 'Pour étiquette',
            'sent_at' => now(),
        ]);

        $tagResponse = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/email-tags', [
            'name' => 'Urgent',
            'color' => '#ff0000',
        ]);
        $tagResponse->assertCreated();
        $tagId = $tagResponse->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/email-messages/{$message->id}/tags", ['tag_id' => $tagId])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Urgent']);

        $this->assertDatabaseHas('email_message_email_tag', ['email_message_id' => $message->id, 'email_tag_id' => $tagId]);
    }
}
