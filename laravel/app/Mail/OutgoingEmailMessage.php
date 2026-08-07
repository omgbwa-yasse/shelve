<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mailable générique pour un message composé depuis la boîte de messagerie
 * interne (`EmailSendService`) — sujet et corps HTML arbitraires, pas de
 * template métier fixe.
 */
class OutgoingEmailMessage extends Mailable
{
    /** @param array<int, array{path: string, filename: string}> $attachmentPaths */
    public function __construct(
        public string $subject,
        public string $bodyHtml,
        public ?Address $from = null,
        public array $attachmentPaths = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: $this->from,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyHtml,
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            fn (array $a) => Attachment::fromPath($a['path'])->as($a['filename']),
            $this->attachmentPaths
        );
    }
}
