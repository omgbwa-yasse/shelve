<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\EmailTag;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Boîte de messagerie IMAP/SMTP (distincte du courrier administratif D06) —
 * couvre les écrans web (settings/comptes, inbox/sent/show/compose/tags)
 * sans toucher un vrai serveur (EmailSyncService/EmailSendService non
 * appelés ici, seuls les CRUD/affichage sont testés).
 */
class EmailMailboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organisation $organisation;
    protected EmailAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::create(['code' => 'ORG-EMAIL', 'name' => 'Org Email', 'email_module_enabled' => true]);

        $this->user = User::create([
            'name' => 'Email Test User',
            'email' => 'email-mailbox-test@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $this->organisation->id,
        ]);
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $this->user->roles()->sync([$role->id]);

        $this->account = EmailAccount::create([
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
            'name' => 'Compte de test',
            'email_address' => 'boite@exemple.com',
            'imap_host' => 'imap.exemple.com',
            'imap_username' => 'boite@exemple.com',
            'imap_password' => 'secret',
            'smtp_host' => 'smtp.exemple.com',
            'smtp_username' => 'boite@exemple.com',
            'smtp_password' => 'secret',
        ]);
    }

    public function test_email_account_settings_pages_load(): void
    {
        $this->actingAs($this->user)->get(route('settings.email-accounts.index'))->assertOk();
        $this->actingAs($this->user)->get(route('settings.email-accounts.create'))->assertOk();
        $this->actingAs($this->user)->get(route('settings.email-accounts.edit', $this->account))->assertOk();
    }

    public function test_creating_an_email_account_encrypts_credentials(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.email-accounts.store'), [
            'name' => 'Nouveau compte',
            'email_address' => 'nouveau@exemple.com',
            'imap_host' => 'imap.nouveau.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => 'nouveau@exemple.com',
            'imap_password' => 'motdepasse-imap',
            'smtp_host' => 'smtp.nouveau.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'nouveau@exemple.com',
            'smtp_password' => 'motdepasse-smtp',
        ]);

        $response->assertRedirect(route('settings.email-accounts.index'));

        $created = EmailAccount::where('email_address', 'nouveau@exemple.com')->first();
        $this->assertNotNull($created);
        $this->assertEquals('motdepasse-imap', $created->imap_password);

        $raw = \DB::table('email_accounts')->where('id', $created->id)->value('imap_password');
        $this->assertNotEquals('motdepasse-imap', $raw); // stocké chiffré, pas en clair
    }

    public function test_inbox_and_sent_pages_load(): void
    {
        EmailMessage::create([
            'email_account_id' => $this->account->id,
            'uid' => 1,
            'folder' => 'INBOX',
            'subject' => 'Bonjour',
            'from_address' => 'expediteur@exemple.com',
            'from_name' => 'Expéditeur',
            'to' => [['mail' => 'boite@exemple.com', 'name' => '']],
            'body_text' => 'Contenu du message',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('mails.email.inbox'))
            ->assertOk()
            ->assertSee('Bonjour');

        $this->actingAs($this->user)->get(route('mails.email.sent'))->assertOk();
    }

    public function test_message_show_page_marks_as_read(): void
    {
        $message = EmailMessage::create([
            'email_account_id' => $this->account->id,
            'uid' => 2,
            'folder' => 'INBOX',
            'subject' => 'Sujet test',
            'from_address' => 'x@exemple.com',
            'body_html' => '<p>Corps <script>alert(1)</script></p>',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('mails.email.show', $message));

        $response->assertOk();
        $this->assertTrue($message->fresh()->is_read);
        // Le HTML de l'email doit être isolé dans un iframe sandboxée, jamais rendu tel quel.
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('sandbox="allow-same-origin"', false);
    }

    public function test_compose_page_loads(): void
    {
        $this->actingAs($this->user)->get(route('mails.email.compose'))->assertOk();
    }

    public function test_mailbox_is_blocked_when_module_disabled(): void
    {
        $this->organisation->update(['email_module_enabled' => false]);

        $this->actingAs($this->user)
            ->get(route('mails.email.inbox'))
            ->assertRedirect(route('settings.email-accounts.index'));
    }

    public function test_settings_page_stays_reachable_when_module_disabled(): void
    {
        $this->organisation->update(['email_module_enabled' => false]);

        $this->actingAs($this->user)->get(route('settings.email-accounts.index'))->assertOk();
    }

    public function test_admin_can_toggle_module_and_account_activation(): void
    {
        $this->assertTrue($this->organisation->email_module_enabled);

        $this->actingAs($this->user)->post(route('settings.email.toggle'))->assertRedirect();
        $this->assertFalse($this->organisation->fresh()->email_module_enabled);

        $this->assertTrue((bool) $this->account->fresh()->is_active);
        $this->actingAs($this->user)->post(route('settings.email-accounts.toggle-active', $this->account))->assertRedirect();
        $this->assertFalse((bool) $this->account->fresh()->is_active);
    }

    public function test_tags_crud(): void
    {
        $this->actingAs($this->user)
            ->post(route('mails.email.tags.manage.store'), ['name' => 'Important', 'color' => '#ff0000'])
            ->assertRedirect();

        $tag = EmailTag::where('name', 'Important')->first();
        $this->assertNotNull($tag);

        $this->actingAs($this->user)->get(route('mails.email.tags.manage.index'))->assertOk()->assertSee('Important');

        $this->actingAs($this->user)
            ->delete(route('mails.email.tags.manage.destroy', $tag))
            ->assertRedirect();

        $this->assertDatabaseMissing('email_tags', ['id' => $tag->id]);
    }
}
