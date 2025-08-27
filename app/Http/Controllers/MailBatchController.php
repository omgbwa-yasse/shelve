
    public function exportPdf(MailBatch $mailBatch)
    {
        $mails = $mailBatch->mails()->with([
            'priority', 'action', 'typology', 'documentType',
            'sender', 'senderOrganisation', 'externalSender', 'externalSenderOrganization',
            'recipient', 'recipientOrganisation', 'externalRecipient', 'externalRecipientOrganization',
            'containers', 'attachments'
        ])->get();

        $data = [
            'mails' => $mails,
            'totalCount' => $mails->count(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mails.print.index', $data);
        return $pdf->download('mail_batch_' . $mailBatch->code . '.pdf');
    }
