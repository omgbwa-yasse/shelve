<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\PublicTemplate;
use Exception;

/**
 * Middleware de sécurité pour les API des templates OPAC
 *
 * Gère la validation des permissions, la limitation du taux de requêtes,
 * et la validation basique du contenu pour les opérations sur templates
 */
class OpacTemplateSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 1. Vérification de l'authentification
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentification requise');
            }

            // 2. Rate limiting spécialisé par type d'opération
            if (!$this->checkRateLimit($request)) {
                return $this->rateLimitResponse();
            }

            // 3. Validation des permissions sur les templates
            if (!$this->validateTemplatePermissions($request)) {
                return $this->forbiddenResponse('Permissions insuffisantes pour cette opération');
            }

            // 4. Validation du contenu pour les opérations sensibles
            if (!$this->validateContent($request)) {
                return $this->badRequestResponse('Contenu non autorisé détecté');
            }

            // 5. Logging des opérations sensibles
            $this->logOperation($request);

            return $next($request);

        } catch (Exception $e) {
            Log::error('Erreur middleware sécurité OPAC: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur de sécurité interne'
            ], 500);
        }
    }

    /**
     * Vérification du rate limiting selon le type d'opération
     */
    private function checkRateLimit(Request $request): bool
    {
        $user = Auth::user();
        $key = 'opac_api_' . $user->id;

        // Rate limits différenciés par opération
        $limits = [
            'auto-save' => ['max' => 60, 'decay' => 60],      // 60/minute pour auto-save
            'preview' => ['max' => 30, 'decay' => 60],        // 30/minute pour preview
            'validate' => ['max' => 20, 'decay' => 60],       // 20/minute pour validation
            'import' => ['max' => 5, 'decay' => 300],         // 5/5min pour import
            'predefined' => ['max' => 10, 'decay' => 60],     // 10/minute pour templates prédéfinis
            'default' => ['max' => 40, 'decay' => 60]         // 40/minute par défaut
        ];

        $operation = $this->getOperationType($request);
        $limit = $limits[$operation] ?? $limits['default'];

        $rateLimitKey = $key . '_' . $operation;

        return RateLimiter::attempt(
            $rateLimitKey,
            $limit['max'],
            function () {
                // Callback vide - on autorise la requête
            },
            $limit['decay']
        );
    }

    /**
     * Déterminer le type d'opération depuis la route
     */
    private function getOperationType(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'auto-save')) return 'auto-save';
        if (str_contains($path, 'preview')) return 'preview';
        if (str_contains($path, 'validate')) return 'validate';
        if (str_contains($path, 'import')) return 'import';
        if (str_contains($path, 'predefined')) return 'predefined';

        return 'default';
    }

    /**
     * Validation des permissions sur les templates
     */
    private function validateTemplatePermissions(Request $request): bool
    {
        $user = Auth::user();

        // Pour les opérations qui nécessitent un template existant
        if ($request->has('template_id')) {
            $templateId = $request->input('template_id');
            $template = PublicTemplate::find($templateId);

            if (!$template) {
                return false;
            }

            // Vérifier si l'utilisateur peut modifier ce template
            return $this->canUserEditTemplate($user, $template);
        }

        // Pour les opérations générales (preview, validate, etc.)
        // Vérifier si l'utilisateur a le droit de gérer les templates
        return $this->canUserManageTemplates($user);
    }

    /**
     * Vérifier si l'utilisateur peut éditer un template spécifique
     */
    private function canUserEditTemplate($user, PublicTemplate $template): bool
    {
        // TODO: Implémenter la logique métier pour les permissions
        // Pour l'instant, on autorise tous les utilisateurs authentifiés

        // Exemple de logique future :
        // - Vérifier si l'utilisateur est propriétaire du template
        // - Vérifier les rôles/permissions
        // - Vérifier si le template est verrouillé

        return true;
    }

    /**
     * Vérifier si l'utilisateur peut gérer les templates en général
     */
    private function canUserManageTemplates($user): bool
    {
        // TODO: Implémenter la logique de permissions basée sur les rôles
        // Exemple: vérifier si l'utilisateur a le rôle "template_manager"

        return true;
    }

    /**
     * Validation du contenu soumis
     */
    private function validateContent(Request $request): bool
    {
        $dangerousPatterns = [
            // Scripts potentiellement malveillants
            '/\beval\s*\(/i',
            '/\bFunction\s*\(/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:/i',

            // Balises et attributs dangereux
            '/<\s*script[^>]*>/i',
            '/on\w+\s*=/i',
            '/<\s*iframe[^>]*>/i',
            '/<\s*object[^>]*>/i',
            '/<\s*embed[^>]*>/i',

            // CSS potentiellement dangereux
            '/expression\s*\(/i',
            '/behavior\s*:/i',
            '/-moz-binding\s*:/i',

            // Tentatives d'inclusion de fichiers externes
            '/@import\s+url\s*\(/i',
            '/url\s*\(\s*[\'"]?https?:\/\//i'
        ];

        // Champs à vérifier
        $fieldsToCheck = ['layout', 'css', 'js', 'template_json'];

        foreach ($fieldsToCheck as $field) {
            if ($request->has($field)) {
                $content = $request->input($field);

                if (is_string($content)) {
                    foreach ($dangerousPatterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            Log::warning('Contenu suspect détecté', [
                                'field' => $field,
                                'pattern' => $pattern,
                                'user_id' => Auth::id(),
                                'ip' => $request->ip()
                            ]);
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Logging des opérations importantes
     */
    private function logOperation(Request $request): void
    {
        $sensitiveOperations = ['auto-save', 'import', 'validate'];
        $operation = $this->getOperationType($request);

        if (in_array($operation, $sensitiveOperations)) {
            Log::info("Opération template OPAC: {$operation}", [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'template_id' => $request->input('template_id'),
                'route' => $request->route()?->getName()
            ]);
        }
    }

    /**
     * Réponse d'erreur d'authentification
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'code' => 'UNAUTHORIZED'
        ], 401);
    }

    /**
     * Réponse d'erreur de rate limiting
     */
    private function rateLimitResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Trop de requêtes. Veuillez patienter.',
            'code' => 'RATE_LIMIT_EXCEEDED'
        ], 429);
    }

    /**
     * Réponse d'erreur de permissions
     */
    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'code' => 'FORBIDDEN'
        ], 403);
    }

    /**
     * Réponse d'erreur de contenu
     */
    private function badRequestResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'code' => 'INVALID_CONTENT'
        ], 400);
    }
}
