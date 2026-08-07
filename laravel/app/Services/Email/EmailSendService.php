<?php

namespace App\Services\Email;

use App\Mail\OutgoingEmailMessage;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Address as MailAddress;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Envoie un email via le SMTP du compte (`EmailAccount`) et conserve une
 * copie locale dans `email_messages` (dossier "Sent") — le mailer Laravel
 * est construit dynamiquement à l'exécution, un compte = une configuration
 * SMTP propre, jamais celle globale de `config/mail.php`.
 *
 * @param array{
 *     to: array<int, string>,
 *     cc?: array<int, string>,
 *     bcc?: array<int, string>,
 *     subject: string,
 *     body_html: string,
 *     in_reply_to?: string|null,
 *     attachments?: array<int, UploadedFile>,
 * } $data
 */
class EmailSendService
{
    public function send(EmailAccount $account, array $data): EmailMessage
    {
        $mailerName = $this->registerDynamicMailer($account);

        $message = EmailMessage::create([
            'email_account_id' => $account->id,
            'folder' => 'Sent',
            'message_id' => null,
            'in_reply_to' => $data['in_reply_to'] ?? null,
            'subject' => $data['subject'],
            'from_address' => $account->email_address,
            'from_name' => $account->default_from_name,
            'to' => $this->toAddressArray($data['to']),
            'cc' => $this->toAddressArray($data['cc'] ?? []),
            'bcc' => $this->toAddressArray($data['bcc'] ?? []),
            'body_html' => $data['body_html'],
            'is_read' => true,
            'is_answered' => false,
            'has_attachments' => ! empty($data['attachments']),
            'sent_at' => now(),
        ]);

        $attachmentPaths = $this->storeAttachments($account, $message, $data['attachments'] ?? []);

        try {
            Mail::mailer($mailerName)
                ->to($data['to'])
                ->cc($data['cc'] ?? [])
                ->bcc($data['bcc'] ?? [])
                ->send(new OutgoingEmailMessage(
                    subject: $data['subject'],
                    bodyHtml: $data['body_html'],
                    from: new MailAddress($account->email_address, $account->default_from_name ?? $account->name),
                    attachmentPaths: $attachmentPaths,
                ));
        } catch (Throwable $e) {
            // L'envoi a échoué : ne pas garder de trace d'un message "Sent" fictif.
            $message->delete();

            throw $e;
        }

        return $message;
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, array{path: string, filename: string}>
     */
    private function storeAttachments(EmailAccount $account, EmailMessage $message, array $files): array
    {
        $result = [];

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $path = "email-attachments/{$account->id}/{$message->id}/{$filename}";

            Storage::disk('local')->putFileAs(
                "email-attachments/{$account->id}/{$message->id}",
                $file,
                $filename
            );

            EmailAttachment::create([
                'email_message_id' => $message->id,
                'filename' => $filename,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'disk' => 'local',
                'path' => $path,
            ]);

            $result[] = ['path' => Storage::disk('local')->path($path), 'filename' => $filename];
        }

        return $result;
    }

    /**
     * @param array<int, string> $emails
     * @return array<int, array{mail: string, name: string}>
     */
    private function toAddressArray(array $emails): array
    {
        return array_map(fn (string $email) => ['mail' => $email, 'name' => ''], $emails);
    }

    private function registerDynamicMailer(EmailAccount $account): string
    {
        $mailerName = "email-account-{$account->id}";

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $account->smtp_host,
            'port' => $account->smtp_port,
            'encryption' => $account->smtp_encryption === 'none' ? null : $account->smtp_encryption,
            'username' => $account->smtp_username,
            'password' => $account->smtp_password,
            'timeout' => 30,
        ]);

        return $mailerName;
    }
}
