<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\AI\PromptTransactionService;
use Illuminate\Support\Facades\DB;
use AiBridge\Facades\AiBridge;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\AI\ProviderRegistry;

class PromptController extends Controller
{
    public function __construct(
        private PromptTransactionService $tx,
        private ProviderRegistry $providers
    ) {}

    public function index(Request $request)
    {
        $q = DB::table('prompts');
        if ($request->filled('is_system')) { $q->where('is_system', (bool)$request->boolean('is_system')); }
        if ($request->filled('organisation_id')) { $q->where('organisation_id', $request->integer('organisation_id')); }
        if ($request->filled('user_id')) { $q->where('user_id', $request->integer('user_id')); }
        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    public function show(int $id)
    {
        $row = DB::table('prompts')->where('id', $id)->first();
        if (!$row) { return response()->json(['message' => 'Not found'], 404); }
        return response()->json($row);
    }

    public function actions(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:reformulate_title,summarize,assign_activity,assign_thesaurus,summarize_slip',
            'entity' => 'required|string|in:record,mail,communication,slip_record',
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'integer|min:1',
            'context' => 'sometimes|array',
            'model' => 'sometimes|string',
            'model_provider' => 'sometimes|string|in:ollama,openai,gemini,claude,openrouter,onn,ollama_turbo,openai_custom',
            'confirm' => 'sometimes|boolean'
        ]);
        $response = null;
        if ($validator->fails()) {
            $response = response()->json(['errors' => $validator->errors()], 422);
        } else {
            $prompt = DB::table('prompts')->where('id', $id)->first();
            if (!$prompt) {
                $response = response()->json(['message' => 'Prompt not found'], 404);
            } else {
                $user = Auth::user();
                $orgId = $prompt->organisation_id ?? ($user->current_organisation_id ?? null);

                $txId = $this->tx->start([
                    'prompt_id' => $prompt->id,
                    'organisation_id' => $orgId,
                    'user_id' => $user?->id,
                    'entity' => $request->string('entity')->toString(),
                    'entity_ids' => $request->input('entity_ids', []),
                    'model' => $request->input('model'),
                    'model_provider' => $request->input('model_provider', 'ollama'),
                ]);

                $t0 = (int) (microtime(true) * 1000);
                try {
                    [$messages, $options] = $this->buildMessagesAndOptions($prompt, $request);
                    $providerName = $options['provider'] ?? 'ollama';
                    $model = $options['model'] ?? config('ollama-laravel.model', 'llama2');
                    $opts = $options; $opts['model'] = $model;

                    // Execute chat using AiBridge provider (with optional streaming)
                    $this->providers->ensureConfigured($providerName);
                    [$text, $usage] = $this->executeAi($providerName, $messages, $opts, $request->boolean('stream'));

                    $t1 = (int) (microtime(true) * 1000);
                    $this->tx->finishSuccess($txId, [
                        'latency_ms' => max(0, $t1 - $t0),
                        'tokens_input' => $usage['prompt_tokens'] ?? null,
                        'tokens_output' => $usage['completion_tokens'] ?? null,
                    ]);

                    $response = response()->json([
                        'status' => 'ok',
                        'transaction_id' => $txId,
                        'output' => $text,
                        'usage' => $usage,
                    ]);
                } catch (\Throwable $e) {
                    $t1 = (int) (microtime(true) * 1000);
                    $this->tx->finishFailure($txId, $e->getMessage(), max(0, $t1 - $t0));
                    Log::error('Prompt action failed', ['tx' => $txId, 'ex' => $e]);
                    $response = response()->json(['status' => 'error', 'transaction_id' => $txId, 'message' => 'AI error'], 500);
                }
            }
        }

        return $response;
    }

    /**
     * Execute chat against AiBridge provider with optional streaming.
     * @return array{0:string,1:array|null} [text, usage]
     */
    private function executeAi(string $providerName, array $messages, array $opts, bool $stream): array
    {
        $provider = AiBridge::provider($providerName);
        if (!$provider) {
            throw new AiProviderNotConfiguredException("AI provider '{$providerName}' not configured.");
        }
        $text = '';
        $usage = null;
        if ($stream && $provider->supportsStreaming()) {
            foreach ($provider->stream($messages, $opts) as $chunk) {
                if (is_array($chunk)) {
                    $text .= $chunk['delta'] ?? ($chunk['content'] ?? json_encode($chunk));
                } else {
                    $text .= (string) $chunk;
                }
            }
        } else {
            $res = $provider->chat($messages, $opts);
            $text = $this->extractText($res);
            $usage = $res['usage'] ?? null;
        }
        return [$text, $usage];
    }

    private function buildMessagesAndOptions(object $prompt, Request $request): array
    {
        $action = $request->string('action')->toString();
        $context = $request->input('context', []);
        $system = $prompt->is_system ? ($prompt->content ?? '') : '';

        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }

        // Load user message template from DB by action title (e.g., action.reformulate_title.user)
        $templateTitle = 'action.' . $action . '.user';
        $tpl = DB::table('prompts')->where('title', $templateTitle)->first();
        if ($tpl && !empty($tpl->content)) {
            $userContent = $this->renderActionTemplate($tpl->content, $action, $context);
        } else {
            // Fallback to minimal inline prompts if template not found
            $userContent = $this->fallbackUserPrompt($action, $context);
        }
        $messages[] = ['role' => 'user', 'content' => $userContent];

    $defaultProvider = app(ProviderRegistry::class)->getSetting('default_provider', 'ollama');
    $defaultModel = app(ProviderRegistry::class)->getSetting('default_model', config('ollama-laravel.model', 'llama2'));
        $options = [
            'provider' => $request->input('model_provider', $defaultProvider),
            'model' => $request->input('model', $defaultModel),
        ];
        return [$messages, $options];
    }

    private function renderActionTemplate(string $template, string $action, array $ctx): string
    {
        $vars = [];
        switch ($action) {
            case 'reformulate_title':
                $vars['title'] = (string) ($ctx['title'] ?? '');
                break;
            case 'summarize':
                $vars['text'] = (string) ($ctx['text'] ?? '');
                break;
            case 'assign_activity':
                $vars['candidates'] = implode(', ', array_map('strval', (array)($ctx['candidates'] ?? [])));
                break;
            case 'assign_thesaurus':
                $vars['pref_labels'] = implode(', ', array_map('strval', (array)($ctx['pref_labels'] ?? [])));
                break;
            case 'summarize_slip':
                $items = (array)($ctx['slip_records'] ?? []);
                $vars['slip_records'] = substr(json_encode($items, JSON_UNESCAPED_UNICODE), 0, 4000);
                break;
            default:
                // no variables
                break;
        }
        // Replace {{var}} placeholders
        $replacements = [];
        foreach ($vars as $k => $v) { $replacements['{{'.$k.'}}'] = $v; }
        return strtr($template, $replacements);
    }

    private function fallbackUserPrompt(string $action, array $ctx): string
    {
        return match ($action) {
            'reformulate_title' => "Reformule ce titre, renvoie uniquement le nouveau titre :\n\nTitre:\n" . ((string)($ctx['title'] ?? '')),
            'summarize' => "Résume ce texte en 3 à 5 phrases (FR) :\n\n" . ((string)($ctx['text'] ?? '')),
            'assign_activity' => "Identifie les activités pertinentes parmi: " . implode(', ', array_map('strval', (array)($ctx['candidates'] ?? []))) . ". Réponds en texte clair.",
            'assign_thesaurus' => "Propose des libellés pertinents à partir de: " . implode(', ', array_map('strval', (array)($ctx['pref_labels'] ?? []))) . ".",
            'summarize_slip' => "Génère un résumé synthétique de ces slips:\n\n" . substr(json_encode((array)($ctx['slip_records'] ?? []), JSON_UNESCAPED_UNICODE), 0, 4000),
            default => 'Provide assistance based on the given context.',
        };
    }

    private function extractText(array $res): string
    {
        $text = null;
        if (!empty($res['content'])) {
            $text = is_array($res['content']) ? implode("", $res['content']) : (string)$res['content'];
        } elseif (!empty($res['message']['content'])) {
            $text = (string)$res['message']['content'];
        } elseif (!empty($res['choices'][0]['message']['content'])) {
            $text = (string)$res['choices'][0]['message']['content'];
        } else {
            $text = json_encode($res);
        }
        return $text;
    }
    // Provider configuration and settings lookup are centralized in ProviderRegistry
}
