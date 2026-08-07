<?php

namespace App\Services\Email;

use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message as ImapMessage;

/**
 * Synchronise un `EmailAccount` avec son serveur IMAP : lit les dossiers
 * distants et met à jour le miroir local `email_messages`/`email_attachments`.
 * N'est jamais la source de vérité — se contente de refléter le serveur.
 */
class EmailSyncService
{
    /**
     * Dossiers IMAP synchronisés par défaut. Les noms varient selon le
     * fournisseur (Gmail utilise "[Gmail]/Sent Mail") — configurable plus
     * tard par compte si besoin ; volontairement figé pour la v1.
     */
    private const DEFAULT_FOLDERS = ['INBOX', 'Sent'];

    /**
     * Nombre de messages les plus récents récupérés par dossier à chaque
     * passage — évite de rapatrier tout l'historique d'un compte au premier
     * sync. Suffisant pour une boîte "vivante" synchronisée régulièrement.
     */
    private const MESSAGES_PER_FOLDER = 100;

    public function sync(EmailAccount $account): void
    {
        $client = $this->buildClient($account);

        try {
            $client->connect();

            foreach (self::DEFAULT_FOLDERS as $folderName) {
                $this->syncFolder($account, $client, $folderName);
            }

            $account->forceFill([
                'last_synced_at' => now(),
                'last_sync_error' => null,
            ])->save();
        } catch (Throwable $e) {
            Log::warning('EmailSyncService: échec de synchronisation', [
                'email_account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            $account->forceFill(['last_sync_error' => $e->getMessage()])->save();

            throw $e;
        } finally {
            if ($client->isConnected()) {
                $client->disconnect();
            }
        }
    }

    private function syncFolder(EmailAccount $account, Client $client, string $folderName): void
    {
        $folder = $client->getFolderByName($folderName) ?? $client->getFolderByPath($folderName, soft_fail: true);

        if (! $folder) {
            return; // Dossier absent chez ce fournisseur (ex. pas de "Sent" séparé) — on ignore.
        }

        $messages = $folder->messages()
            ->leaveUnread() // ne modifie pas le flag \Seen côté serveur en lisant le message
            ->fetchOrderDesc()
            ->limit(self::MESSAGES_PER_FOLDER)
            ->get();

        foreach ($messages as $imapMessage) {
            $this->storeMessage($account, $folderName, $imapMessage);
        }
    }

    private function storeMessage(EmailAccount $account, string $folderName, ImapMessage $imapMessage): void
    {
        $uid = $imapMessage->uid;

        $from = $imapMessage->from?->first();
        $flags = $imapMessage->getFlags();

        $message = EmailMessage::updateOrCreate(
            ['email_account_id' => $account->id, 'folder' => $folderName, 'uid' => $uid],
            [
                'message_id' => (string) ($imapMessage->message_id ?? ''),
                'in_reply_to' => (string) ($imapMessage->in_reply_to ?? '') ?: null,
                'subject' => (string) ($imapMessage->subject ?? ''),
                'from_address' => $from?->mail,
                'from_name' => $from?->personal,
                'to' => $this->addressesToArray($imapMessage->to),
                'cc' => $this->addressesToArray($imapMessage->cc),
                'bcc' => $this->addressesToArray($imapMessage->bcc),
                'body_html' => $imapMessage->hasHTMLBody() ? $imapMessage->getHTMLBody() : null,
                'body_text' => $imapMessage->hasTextBody() ? $imapMessage->getTextBody() : null,
                'is_read' => $flags->contains('seen') || $flags->contains('Seen'),
                'is_flagged' => $flags->contains('flagged') || $flags->contains('Flagged'),
                'is_answered' => $flags->contains('answered') || $flags->contains('Answered'),
                'has_attachments' => $imapMessage->hasAttachments(),
                'sent_at' => $imapMessage->date?->toDate(),
            ]
        );

        if ($imapMessage->hasAttachments() && $message->attachments()->count() === 0) {
            $this->storeAttachments($account, $message, $imapMessage);
        }
    }

    private function storeAttachments(EmailAccount $account, EmailMessage $message, ImapMessage $imapMessage): void
    {
        foreach ($imapMessage->getAttachments() as $attachment) {
            $filename = $attachment->getName() ?: 'piece-jointe';
            $path = "email-attachments/{$account->id}/{$message->id}/{$filename}";

            Storage::disk('local')->put($path, $attachment->getContent());

            EmailAttachment::create([
                'email_message_id' => $message->id,
                'filename' => $filename,
                'mime_type' => $attachment->getContentType(),
                'size' => $attachment->getSize(),
                'disk' => 'local',
                'path' => $path,
            ]);
        }
    }

    /**
     * @return array<int, array{mail: string, name: string}>
     */
    private function addressesToArray($attribute): array
    {
        if (! $attribute) {
            return [];
        }

        return collect($attribute->all())
            ->map(fn ($address) => ['mail' => $address->mail, 'name' => $address->personal])
            ->values()
            ->all();
    }

    private function buildClient(EmailAccount $account): Client
    {
        $manager = new ClientManager();

        return $manager->make([
            'host' => $account->imap_host,
            'port' => $account->imap_port,
            'protocol' => 'imap',
            'encryption' => $account->imap_encryption === 'none' ? false : $account->imap_encryption,
            'validate_cert' => true,
            'username' => $account->imap_username,
            'password' => $account->imap_password,
            'authentication' => null,
            'timeout' => 30,
        ]);
    }
}
