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
        $mail = Mail::with(['attachments'])->findOrFail($mailId);
        $aiBuilder = new AiMessageBuilder();
        $defaultValues = app(\App\Services\AI\DefaultValueService::class);
        $provider = $defaultValues->getDefaultProvider();
        $model = $defaultValues->getDefaultModel();
        $options = $aiBuilder->buildMailSummaryOptions($provider, $model);
        $messages = $aiBuilder->buildMailSummaryMessages($mail);
        // Here, you would call your AI service with $messages and $options
        // For now, just return the messages and options for debugging
        return response()->json([
            'messages' => $messages,
            'options' => $options,
        ]);
    }
}
