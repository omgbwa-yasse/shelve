<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PromptActionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Prompt;
use Illuminate\Http\JsonResponse;
use App\Services\AI\PromptTransactionService;
use App\Services\AI\AiMessageBuilder;
use App\Services\AI\DefaultValueService;
use AiBridge\Facades\AiBridge;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\AI\ProviderRegistry;

class PromptController extends Controller
{
    public function __construct(
        private PromptTransactionService $tx,
        private ProviderRegistry $providers,
        private AiMessageBuilder $builder,
        private DefaultValueService $defaultValues
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Prompt::query();
        if ($request->filled('is_system')) { $q->where('is_system', $request->boolean('is_system')); }
        if ($request->filled('organisation_id')) { $q->where('organisation_id', $request->integer('organisation_id')); }
        if ($request->filled('user_id')) { $q->where('user_id', $request->integer('user_id')); }
        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    public function show(int $id): JsonResponse
    {
        $row = Prompt::find($id);
        if (!$row) { return response()->json(['message' => 'Not found'], 404); }
        return response()->json($row);
    }

    /**
     * Perform an action using the specified prompt.
     */
    public function actions(PromptActionRequest $request, int $id): JsonResponse
    {
        $prompt = Prompt::find($id);
        if (!$prompt) { return response()->json(['message' => 'Prompt not found'], 404); }

        $user = Auth::user();
        $orgId = $prompt->organisation_id ?? ($user->current_organisation_id ?? null);

        $txId = $this->tx->start([
            'prompt_id' => $prompt->id,
            'organisation_id' => $orgId,
            'user_id' => $user?->id,
            'entity' => $request->string('entity')->toString(),
            'entity_ids' => $request->input('entity_ids', []),
            'model' => $this->defaultValues->getDefaultModel($request->input('model')),
            'model_provider' => $this->defaultValues->getValidatedProvider($request->input('model_provider')),
        ]);

        $t0 = (int) (microtime(true) * 1000);
        try {
            [$messages, $options] = $this->builder->build($prompt, $request);
            $providerName = $this->defaultValues->getValidatedProvider($options['provider'] ?? null);
            $model = $this->defaultValues->getDefaultModel($options['model'] ?? null);
            $opts = $options;
            $opts['model'] = $model;

            $this->providers->ensureConfigured($providerName);
            [$text, $usage] = $this->executeAi($providerName, $messages, $opts, $request->boolean('stream'));

            $t1 = (int) (microtime(true) * 1000);
            $this->tx->finishSuccess($txId, [
                'latency_ms' => max(0, $t1 - $t0),
                'tokens_input' => $usage['prompt_tokens'] ?? null,
                'tokens_output' => $usage['completion_tokens'] ?? null,
            ]);

            return response()->json([
                'status' => 'ok',
                'transaction_id' => $txId,
                'output' => $text,
                'usage' => $usage,
            ]);
        } catch (\Throwable $e) {
            $t1 = (int) (microtime(true) * 1000);
            $this->tx->finishFailure($txId, $e->getMessage(), max(0, $t1 - $t0));
            Log::error('Prompt action failed', ['tx' => $txId, 'ex' => $e]);
            return response()->json([
                'status' => 'error',
                'transaction_id' => $txId,
                'message' => $e->getMessage() ?: 'AI error',
            ], ($e instanceof AiProviderNotConfiguredException) ? 422 : 500);
        }
    }

    private function executeAi(string $providerName, array $messages, array $opts, bool $stream): array
    {
        $provider = AiBridge::provider($providerName);
        if (!$provider) {
            try { $this->providers->ensureConfigured($providerName); } catch (\Throwable) { /* ignore */ }
            $provider = AiBridge::provider($providerName);
            if (!$provider) { throw new AiProviderNotConfiguredException("AI provider '{$providerName}' not configured."); }
        }

        [$systemContent, $userContents] = $this->parseMessages($messages);
        $model = $opts['model'] ?? null;

        $builderResult = $this->tryExecuteWithBuilder($provider, $model, $systemContent, $userContents, $opts, $stream);
        if ($builderResult !== null) { return $builderResult; }

        $text = '';
        $usage = null;
        if ($stream && method_exists($provider, 'supportsStreaming') && $provider->supportsStreaming()) {
            foreach ($provider->stream($messages, $opts) as $chunk) {
                $text .= is_array($chunk) ? ($chunk['delta'] ?? ($chunk['content'] ?? json_encode($chunk))) : (string)$chunk;
            }
        } else {
            $res = $provider->chat($messages, $opts);
            $text = $this->builder->extractText($res);
            $usage = $res['usage'] ?? null;
        }
        return [$text, $usage];
    }

    private function parseMessages(array $messages): array
    {
        $systemContent = '';
        $userContents = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? null; $content = $m['content'] ?? '';
            if ($role === 'system' && $content) { $systemContent = (string) $content; }
            elseif ($role === 'user' && $content) { $userContents[] = (string) $content; }
        }
        return [$systemContent, $userContents];
    }

    private function tryExecuteWithBuilder(object $provider, ?string $model, string $systemContent, array $userContents, array $opts, bool $stream): ?array
    {
        try {
            $builder = $this->makeChatBuilder($provider);
            if (!$builder) { return null; }

            $builder = $this->configureBuilder($builder, $model, $opts, $systemContent, $userContents);
            [$text, $usage] = $this->sendBuilder($builder, $stream);
            return [$text, $usage];
        } catch (\Throwable $e) {
            Log::warning('AiBridge new API path failed, falling back to legacy', ['ex' => $e->getMessage()]);
            return null;
        }
    }

    private function makeChatBuilder(object $provider): ?object
    {
        foreach (['newChat','chatRequest','chatBuilder'] as $factory) {
            if (method_exists($provider, $factory)) {
                try { return $provider->{$factory}(); } catch (\Throwable) { /* ignore factory error and try next */ }
            }
        }
        return null;
    }

    private function configureBuilder(object $builder, ?string $model, array $opts, string $systemContent, array $userContents): object
    {
        $builder = $this->applyModel($builder, $model);
        $builder = $this->applyOptions($builder, $opts);
        $builder = $this->applySystemAndMessages($builder, $systemContent, $userContents);
        return $builder;
    }

    private function applyModel(object $builder, ?string $model): object
    {
        if ($model && method_exists($builder, 'model')) {
            return $builder->model($model);
        }
        return $builder;
    }

    private function applyOptions(object $builder, array $opts): object
    {
        if (!$opts) { return $builder; }
        $map = [
            'temperature' => ['temperature','setTemperature'],
            'top_p' => ['topP','setTopP'],
            'top_k' => ['topK','setTopK'],
            'repeat_penalty' => ['repeatPenalty','setRepeatPenalty'],
        ];
        foreach ($map as $optKey => $methods) {
            if (!array_key_exists($optKey, $opts)) { continue; }
            $builder = $this->applyOneOption($builder, $methods, $opts[$optKey]);
        }
        return $builder;
    }

    private function applyOneOption(object $builder, array $methods, $value): object
    {
        foreach ($methods as $meth) {
            if (method_exists($builder, $meth)) {
                return $builder->{$meth}($value);
            }
        }
        return $builder;
    }

    private function applySystemAndMessages(object $builder, string $systemContent, array $userContents): object
    {
        if ($systemContent && method_exists($builder, 'system')) {
            $builder = $builder->system($systemContent);
        }
        foreach ($userContents as $uc) {
            if (method_exists($builder, 'user')) {
                $builder = $builder->user($uc);
            } elseif (method_exists($builder, 'message')) {
                $builder = $builder->message('user', $uc);
            }
        }
        return $builder;
    }

    private function sendBuilder(object $builder, bool $stream): array
    {
        $text = '';
        $usage = null;
        if ($stream && method_exists($builder, 'sendStream')) {
            foreach ($builder->sendStream() as $chunk) {
                $text .= is_array($chunk) ? ($chunk['delta'] ?? ($chunk['content'] ?? json_encode($chunk))) : (string)$chunk;
            }
            return [$text, $usage];
        }
        if ($stream && method_exists($builder, 'enableStreaming')) { $builder = $builder->enableStreaming(); }
        if (!method_exists($builder, 'send')) { return ['', null]; }
        $res = $builder->send();
        if (is_array($res)) { $text = $this->builder->extractText($res); $usage = $res['usage'] ?? null; }
        elseif (is_string($res)) { $text = $res; }
        elseif (is_object($res)) { $text = (string) ($res->content ?? ''); $usage = $res->usage ?? null; }
        return [$text, $usage];
    }
}
