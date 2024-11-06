<?php

namespace App\Exports;

use App\Models\Mail; // Importer le modèle Mail
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Ajouter WithMapping

class MailsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $mails; // Renommer la variable en $mails

    public function __construct($mails)
    {
        $this->mails = $mails;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom du Courrier',
            'Action',
            'Description',
            'Expéditeur (Utilisateur)',
            'Expéditeur (Organisation)',
            'Destinataire (Utilisateur)',
            'Destinataire (Organisation)',
            'Type de document',
            'Date'
        ];
    }

    public function collection()
    {
        return $this->mails; // Retourner directement la collection de mails
    }

    public function map($mail): array // Ajouter la méthode map()
    {
        return [
            $mail->code,
            $mail->name,
            $mail->action->name ?? 'N/A',
            $mail->description,
            $mail->sender->name ?? 'N/A',
            $mail->senderOrganisation->name ?? 'N/A',
            $mail->recipient->name ?? 'N/A',
            $mail->recipientOrganisation->name ?? 'N/A',
            $mail->document_type,
            $mail->date->format('d/m/Y') // Formater la date
        ];
    }
}
