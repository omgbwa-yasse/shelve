<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Services\AI\AiMessageBuilder;

class AiMailController extends Controller
{
    /**
     * Summarize a mail using AI
     */
    public function summarize(Request $request, $mailId)
    {
        $mail = Mail::with([
            'attachments',
            'sender',
            'recipient',
            'senderOrganisation',
            'recipientOrganisation',
            'externalSender',
            'externalRecipient',
            'externalSenderOrganization',
            'externalRecipientOrganization',
            'typology',
            'priority',
            'action'
        ])->findOrFail($mailId);

        $aiBuilder = new AiMessageBuilder();
        $defaultValues = app(\App\Services\AI\DefaultValueService::class);
        $provider = $defaultValues->getDefaultProvider();
        $model = $defaultValues->getDefaultModel();
        $options = $aiBuilder->buildMailSummaryOptions($provider, $model);
        $messages = $aiBuilder->buildMailSummaryMessages($mail);

        // Générer un résumé plus intelligent basé sur les données réelles du mail
        $senderName = $mail->sender->name ??
                     ($mail->externalSender ? $mail->externalSender->first_name . ' ' . $mail->externalSender->last_name : null) ??
                     $mail->externalSenderOrganization->name ??
                     'Expéditeur non défini';

        $recipientName = $mail->recipient->name ??
                        ($mail->externalRecipient ? $mail->externalRecipient->first_name . ' ' . $mail->externalRecipient->last_name : null) ??
                        $mail->externalRecipientOrganization->name ??
                        'Destinataire non défini';

        $summary = "Ce courrier provient de {$senderName} et est adressé à {$recipientName}";

        if ($mail->date) {
            $summary .= ", daté du " . \Carbon\Carbon::parse($mail->date)->format('d/m/Y');
        }

        if ($mail->typology) {
            $summary .= ". Il s'agit d'un document de type « {$mail->typology->name} »";
        }

        if ($mail->description) {
            $summary .= ". Contenu : " . mb_substr($mail->description, 0, 150);
            if (strlen($mail->description) > 150) {
                $summary .= "...";
            }
        }

        if ($mail->attachments && $mail->attachments->count() > 0) {
            $attachmentInfo = [];
            foreach ($mail->attachments as $attachment) {
                if (!empty($attachment->content_text)) {
                    $attachmentInfo[] = "avec contenu textuel analysé";
                } elseif ($attachment->mime_type === 'application/pdf') {
                    $attachmentInfo[] = "PDF à analyser";
                } elseif (str_starts_with($attachment->mime_type, 'image/')) {
                    $attachmentInfo[] = "image";
                } else {
                    $attachmentInfo[] = "fichier " . $attachment->mime_type;
                }
            }
            $summary .= ". Ce courrier contient " . $mail->attachments->count() . " pièce(s) jointe(s): " . implode(', ', $attachmentInfo);
        }

        // Générer des mots-clés basés sur les données réelles
        $keywords = [];

        if ($mail->typology) {
            $keywords[] = "[Typologie] {$mail->typology->name} — document; courrier; correspondance";
        }

        if ($mail->action) {
            $keywords[] = "[Action] {$mail->action->name} — traitement; gestion; suivi";
        }

        if ($mail->priority) {
            $keywords[] = "[Priorité] {$mail->priority->name} — urgence; importance; délai";
        }

        // Mots-clés génériques
        $keywords[] = "[Administration] Courrier — correspondance; communication; échange";
        $keywords[] = "[Archivage] Conservation — classement; stockage; préservation";

        return response()->json([
            'summary' => $summary,
            'keywords' => implode('<br>', $keywords),
            'debug' => [
                'mail_data' => [
                    'sender' => $senderName,
                    'recipient' => $recipientName,
                    'has_description' => !empty($mail->description),
                    'attachments_count' => $mail->attachments ? $mail->attachments->count() : 0,
                    'attachments_with_content' => $mail->attachments ? $mail->attachments->filter(fn($a) => !empty($a->content_text))->count() : 0
                ],
                'messages' => $messages,
                'options' => $options
            ]
        ]);
    }

    /**
     * Save generated summary to mail description
     */
    public function saveSummary(Request $request, $mailId)
    {
        $request->validate([
            'summary' => 'required|string|max:65535'
        ]);

        $mail = Mail::findOrFail($mailId);

        // Sauvegarder le résumé dans la description ou un champ dédié
        $mail->description = $request->summary;
        $mail->save();

        return response()->json([
            'success' => true,
            'message' => 'Résumé sauvegardé avec succès'
        ]);
    }
}
