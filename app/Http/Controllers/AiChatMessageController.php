<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class AiChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $messages = AiChatMessage::with(['chat'])->paginate(15);
        return view('ai.chats.messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('ai.chats.messages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'role' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'nullable|json',
        ]);

        AiChatMessage::create($validated);

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message created successfully');
    }

    /**
     * Store a message for a specific chat and generate an AI response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chatId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function storeForChat(Request $request, $chatId)
    {
        try {
            $chat = \App\Models\AiChat::with('aiModel')->findOrFail($chatId);

            $validated = $request->validate([
                'content' => 'required|string',
                'role' => 'required|string',
            ]);

            // Vérifier si le chat est actif
            if (!$chat->is_active) {
                throw new \Exception("Ce chat est inactif et ne peut pas recevoir de nouveaux messages.");
            }

            // Vérifier si un modèle AI est associé au chat
            if (!$chat->aiModel) {
                throw new \Exception("Aucun modèle AI n'est associé à ce chat.");
            }

            // Ajouter l'ID du chat
            $validated['ai_chat_id'] = $chat->id;

            // Créer le message de l'utilisateur
            $userMessage = AiChatMessage::create($validated);

            // Si c'est un message utilisateur, générer une réponse de l'IA
            $aiMessage = null;
            if ($validated['role'] === 'user') {
                // Récupérer le service Ollama une seule fois pour éviter la duplication
                $ollamaService = app(\App\Services\OllamaService::class);

                // Vérifier l'état de santé d'Ollama
                $healthCheck = $ollamaService->healthCheck();
                if ($healthCheck['status'] !== 'healthy') {
                    throw new \Exception("Le service Ollama n'est pas disponible: {$healthCheck['message']}");
                }

                try {
                    // Récupérer le contexte des messages précédents
                    $previousMessages = $ollamaService->getChatHistory($chat, 10);

                    // Créer un message temporaire indiquant que l'IA réfléchit
                    $tempMessage = AiChatMessage::create([
                        'ai_chat_id' => $chat->id,
                        'role' => 'assistant',
                        'content' => '...',
                        'metadata' => [
                            'type' => 'thinking',
                            'timestamp' => now()->timestamp
                        ]
                    ]);

                    // Faire appel au service Ollama pour obtenir une réponse
                    $response = $ollamaService->generate(
                        $chat->aiModel->name,
                        $validated['content'],
                        [
                            'temperature' => 0.7,
                            'context' => $previousMessages
                        ]
                    );

                    // Vérifier si la réponse est un succès
                    if (!isset($response['success']) || $response['success'] !== true) {
                        throw new \Exception("Erreur lors de la génération de la réponse: " . ($response['error'] ?? 'Réponse invalide'));
                    }

                    // Mettre à jour le message avec la réponse de l'IA
                    $tempMessage->update([
                        'content' => $response['response'] ?? 'Je ne sais pas comment répondre à cette question.',
                        'metadata' => [
                            'type' => 'response',
                            'timestamp' => now()->timestamp,
                            'evaluation_time' => $response['eval_time'] ?? null,
                            'total_duration' => $response['total_duration'] ?? null
                        ]
                    ]);

                    $aiMessage = $tempMessage->fresh();
                } catch (\Exception $e) {
                    // Log l'erreur pour le débogage
                    Log::error('Erreur de génération AI: ' . $e->getMessage(), [
                        'chat_id' => $chat->id,
                        'model' => $chat->aiModel->name ?? 'unknown',
                        'exception' => $e
                    ]);

                    // En cas d'erreur, créer un message d'erreur
                    $aiMessage = AiChatMessage::create([
                        'ai_chat_id' => $chat->id,
                        'role' => 'assistant',
                        'content' => "Désolé, je n'ai pas pu traiter votre demande. Erreur: " . $e->getMessage(),
                        'metadata' => [
                            'type' => 'error',
                            'timestamp' => now()->timestamp
                        ]
                    ]);
                }
            }

            // Si c'est une requête AJAX, retourner une réponse JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'userMessage' => $userMessage,
                    'aiMessage' => $aiMessage
                ]);
            }

            return redirect()->route('ai.chats.show', ['chat' => $chatId])->with('success', 'Message envoyé avec succès !');
        } catch (\Exception $e) {
            // Log l'erreur
            Log::error('Erreur storeForChat: ' . $e->getMessage());

            // Si c'est une requête AJAX, retourner une réponse JSON avec l'erreur
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }

            return redirect()->route('ai.chats.show', ['chat' => $chatId])->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Contracts\View\View
     */
    public function show(AiChatMessage $aiChatMessage)
    {
        return view('ai.chats.messages.show', compact('aiChatMessage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(AiChatMessage $aiChatMessage)
    {
        return view('ai.chats.messages.edit', compact('aiChatMessage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AiChatMessage $aiChatMessage)
    {
        $validated = $request->validate([
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'role' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'nullable|json',
        ]);

        $aiChatMessage->update($validated);

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AiChatMessage $aiChatMessage)
    {
        $aiChatMessage->delete();

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message deleted successfully');
    }
}
