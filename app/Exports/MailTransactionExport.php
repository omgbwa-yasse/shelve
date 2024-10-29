<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MailTransactionExport implements FromCollection, WithHeadings
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom du Courrier',
            'Action',
            'Description',
            'Envoyé par',
            'Reçu par',
            'Type de document',
            'Date'
        ];
    }

    public function collection()
    {
        return $this->transactions->map(function ($transaction) {
            return [
                $transaction->code,
                $transaction->mail->name,
                $transaction->action->name,
                $transaction->description,
                $transaction->userSend->name . ' (' . $transaction->organisationSend->name . ')',
                $transaction->userReceived->name . ' (' . $transaction->organisationReceived->name . ')',
                $transaction->documentType->name,
                $transaction->date_creation ? \Carbon\Carbon::parse($transaction->date_creation)->format('d/m/Y') : null
            ];
        });
    }
}
