<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Services\AI\AiMessageBuilder;
use App\Services\AI\DefaultValueService;
use App\Services\AI\ProviderRegistry;
use App\Services\AI\ResponseTextExtractor;
use AiBridge\Facades\AiBridge;
use Illuminate\Support\Facades\Log;

class AiMailController extends Controller
{
    /**
     * Summarize a mail using AI (appel LLM réel, avec repli sur un template déterministe)
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
        $defaultValues = app(DefaultValueService::class);
        $provider = $defaultValues->getDefaultProvider();
        $model = $defaultValues->getDefaultModel();
        $messages = $aiBuilder->buildMailSummaryMessages($mail);
        $options = $aiBuilder->buildMailSummaryOptions($provider, $model);

        // Appel LLM réel
        $llmSummary = null;
        $llmError = null;

        try {
            app(ProviderRegistry::class)->ensureConfigured($provider);
            $res = AiBridge::provider($provider)->chat($messages, $options);
            $content = ResponseTextExtractor::extract($res);
            if (is_string($content) && trim($content) !== '') {
                $llmSummary = trim($content);
            }
        } catch (\Throwable $e) {
            Log::warning("AI mail summarize échoué, repli sur le template", ['error' => $e->getMessage()]);
            $llmError = $e->getMessage();
        }

        $summary = $llmSummary ?? $this->buildFallbackSummary($mail);

        return response()->json([
            'summary' => $summary,
            'keywords' => $this->buildFallbackKeywords($mail),
            'ai_used' => $llmSummary !== null,
            'debug' => [
                'ai_error' => $llmError,
                'mail_data' => [
                    'sender' => $this->senderName($mail),
                    'recipient' => $this->recipientName($mail),
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

        $mail->description = $request->summary;
        $mail->save();

        return response()->json([
            'success' => true,
            'message' => 'Résumé sauvegardé avec succès'
        ]);
    }

    private function senderName(Mail $mail): string
    {
        return $mail->sender->name ??
               ($mail->externalSender ? $mail->externalSender->first_name . ' ' . $mail->externalSender->last_name : null) ??
               $mail->externalSenderOrganization->name ??
               'Expéditeur non défini';
    }

    private function recipientName(Mail $mail): string
    {
        return $mail->recipient->name ??
               ($mail->externalRecipient ? $mail->externalRecipient->first_name . ' ' . $mail->externalRecipient->last_name : null) ??
               $mail->externalRecipientOrganization->name ??
               'Destinataire non défini';
    }

    private function buildFallbackSummary(Mail $mail): string
    {
        $summary = "Ce courrier provient de {$this->senderName($mail)} et est adressé à {$this->recipientName($mail)}";

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

        return $summary;
    }

    private function buildFallbackKeywords(Mail $mail): string
    {
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

        $keywords[] = "[Administration] Courrier — correspondance; communication; échange";
        $keywords[] = "[Archivage] Conservation — classement; stockage; préservation";

        return implode('<br>', $keywords);
    }
}
