<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\PublicRecordApiController;
use App\Http\Controllers\Api\PublicEventApiController;
use App\Http\Controllers\Api\PublicNewsApiController;
use App\Http\Controllers\Api\PublicUserApiController;
use App\Http\Controllers\Api\PublicDocumentRequestApiController;
use App\Http\Controllers\Api\PublicFeedbackApiController;
use App\Http\Controllers\Api\PublicChatApiController;
use App\Http\Controllers\Api\PublicPageApiController;
use App\Http\Controllers\Api\PublicTemplateApiController;
use App\Http\Controllers\Api\PublicSearchLogApiController;
use App\Http\Controllers\Api\PublicResponseApiController;
use App\Http\Controllers\Api\PublicResponseAttachmentApiController;
use App\Http\Controllers\Api\PublicRecordAttachmentApiController;
use App\Http\Controllers\Api\PublicChatParticipantApiController;
use App\Http\Controllers\Api\PublicChatMessageApiController;
use App\Http\Controllers\Api\PublicEventRegistrationApiController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\Api\ContainerSearchController;
use App\Http\Controllers\Api\RecordDigitalFolderApiController;
use App\Http\Controllers\Api\RecordDigitalDocumentApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\CommunicabilityController;
use App\Http\Controllers\Api\V1\KeywordController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\LawArticleController as ApiLawArticleController;
use App\Http\Controllers\Api\V1\LawController as ApiLawController;
use App\Http\Controllers\Api\V1\SortController as ApiSortController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\AuthorContactController;
use App\Http\Controllers\Api\V1\ExternalContactController;
use App\Http\Controllers\Api\V1\ExternalOrganizationController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SettingCategoryController;
use App\Http\Controllers\Api\V1\ReferenceListController;
use App\Http\Controllers\Api\V1\BuildingController;
use App\Http\Controllers\Api\V1\FloorController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\ShelfController;
use App\Http\Controllers\Api\V1\ContainerController;
use App\Http\Controllers\Api\V1\ContainerPropertyController;
use App\Http\Controllers\Api\V1\ContainerStatusController;

/*
|--------------------------------------------------------------------------
| API v1 — authentification des agents
|--------------------------------------------------------------------------
| Phase 1 de la migration, étape 1.0.4.
| Contrat : contracts/CONVENTIONS.md §6.
|
| Le guard `web` (session) reste actif pour le back-office Blade : les deux
| mécanismes coexistent jusqu'à la bascule du frontal (phase 2, étape 2.3).
*/
Route::prefix('v1/auth')->name('api.v1.auth.')->group(function () {
    // 5 tentatives par heure : même quota que le login du portail public
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('rate.limit:auth,5,60')
        ->name('login');

    Route::middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        Route::post('switch-organisation', [AuthController::class, 'switchOrganisation'])
            ->name('switch-organisation');
    });
});

/*
|--------------------------------------------------------------------------
| API v1 — D01 Référentiels (sous-lot 1)
|--------------------------------------------------------------------------
| Phase 1, étape 1.1. Contrat : contracts/CONVENTIONS.md.
| Les alias `Api*Controller` évitent toute collision de nom avec les
| contrôleurs Blade du même nom, encore actifs dans routes/web.php.
*/
Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
    // Actions métier déclarées AVANT les apiResource : `list`, `hierarchy`, `search`
    // ne doivent pas être capturées par le paramètre `{activity}` / `{keyword}`.
    Route::get('activities/list', [ActivityController::class, 'list'])->name('activities.list');
    Route::get('activities/hierarchy/{activity?}', [ActivityController::class, 'hierarchy'])->name('activities.hierarchy');
    Route::get('keywords/search', [KeywordController::class, 'search'])->name('keywords.search');
    Route::post('keywords/process', [KeywordController::class, 'processKeywords'])->name('keywords.process');
    Route::post('languages/{locale}/activate', [LanguageController::class, 'activate'])->name('languages.activate');

    Route::apiResource('activities', ActivityController::class)->except(['create', 'edit']);
    Route::apiResource('communicabilities', CommunicabilityController::class)->except(['create', 'edit']);
    Route::apiResource('keywords', KeywordController::class)->except(['create', 'edit']);
    Route::apiResource('languages', LanguageController::class)->except(['create', 'edit']);
    Route::apiResource('sorts', ApiSortController::class)->except(['create', 'edit']);
    Route::apiResource('laws', ApiLawController::class)->except(['create', 'edit']);
    Route::apiResource('law-articles', ApiLawArticleController::class)->except(['create', 'edit']);

    // D01 — sous-lot 2 (porté le 2026-08-04).
    Route::apiResource('authors', AuthorController::class)->except(['create', 'edit']);
    Route::get('author-types', [AuthorController::class, 'authorTypes'])->name('author-types.index');
    Route::apiResource('author-contacts', AuthorContactController::class)->except(['create', 'edit']);
    Route::apiResource('external-contacts', ExternalContactController::class)->except(['create', 'edit']);
    Route::apiResource('external-organizations', ExternalOrganizationController::class)->except(['create', 'edit']);
    Route::apiResource('settings', SettingController::class)->except(['create', 'edit']);
    Route::post('settings/{setting}/set-value', [SettingController::class, 'setValue'])->name('settings.set-value');
    Route::delete('settings/{setting}/reset-value', [SettingController::class, 'resetValue'])->name('settings.reset-value');
    // `tree` précède l'apiResource : sinon `{setting_category}` capturerait `tree`.
    Route::get('setting-categories/tree', [SettingCategoryController::class, 'tree'])->name('setting-categories.tree');
    Route::apiResource('setting-categories', SettingCategoryController::class)->except(['create', 'edit']);
    Route::apiResource('reference-lists', ReferenceListController::class)->except(['create', 'edit']);
    Route::post('reference-lists/{referenceList}/values', [ReferenceListController::class, 'addValue'])->name('reference-lists.values.store');
    Route::patch('reference-lists/{referenceList}/values/{value}', [ReferenceListController::class, 'updateValue'])->name('reference-lists.values.update');
    Route::delete('reference-lists/{referenceList}/values/{value}', [ReferenceListController::class, 'deleteValue'])->name('reference-lists.values.destroy');

    // D03 — Localisation physique (porté le 2026-08-04).
    Route::apiResource('buildings', BuildingController::class)->except(['create', 'edit']);
    Route::apiResource('floors', FloorController::class)->except(['create', 'edit']);
    Route::apiResource('rooms', RoomController::class)->except(['create', 'edit']);
    Route::apiResource('shelves', ShelfController::class)->except(['create', 'edit']);
    Route::apiResource('containers', ContainerController::class)->except(['create', 'edit']);
    Route::apiResource('container-properties', ContainerPropertyController::class)->except(['create', 'edit']);
    Route::apiResource('container-statuses', ContainerStatusController::class)->except(['create', 'edit']);
});

/*
|--------------------------------------------------------------------------
| API v1 — domaines restants (D02, D04…D16)
|--------------------------------------------------------------------------
| Chaque domaine dispose d'un fichier de routes dédié (routes/api/{Dxx}.php),
| inclus dans un groupe portant le même préfixe, nommage et middleware que le
| groupe principal. Les fichiers sont remplis au fil du portage (étape 1.x).
*/
Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
    require __DIR__ . '/api/D02.php';
    require __DIR__ . '/api/D04.php';
    require __DIR__ . '/api/D05.php';
    require __DIR__ . '/api/D06.php';
    require __DIR__ . '/api/D07.php';
    require __DIR__ . '/api/D08.php';
    require __DIR__ . '/api/D09.php';
    require __DIR__ . '/api/D10.php';
    require __DIR__ . '/api/D11.php';
    require __DIR__ . '/api/D12.php';
    require __DIR__ . '/api/D13.php';
    require __DIR__ . '/api/D14.php';
    require __DIR__ . '/api/D16.php';
});

// Public API routes with rate limiting
Route::prefix('public')->name('api.public.')->middleware('rate.limit:api_general,100,60')->group(function () {
    $authRateLimit = 'rate.limit:auth,3,60';
    // Records - Services ouverts (rate limit plus restrictif)
    Route::apiResource('records', PublicRecordApiController::class)
        ->names('records')
        ->except(['create', 'edit'])
        ->middleware('rate.limit:search,50,60'); // 50 recherches par heure pour les records

    // Users (Authentication) - rate limit très restrictif
    Route::post('users/login', [PublicUserApiController::class, 'login'])
        ->name('users.login')
        ->middleware('rate.limit:auth,5,60'); // 5 tentatives de connexion par heure

    Route::post('users/register', [PublicUserApiController::class, 'register'])
        ->name('users.register')
        ->middleware($authRateLimit); // 3 inscriptions par heure

    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])
        ->name('users.verify-token')
        ->middleware('rate.limit:auth,10,60'); // 10 vérifications par heure

    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])
        ->name('users.forgot-password')
        ->middleware($authRateLimit); // 3 demandes de reset par heure

    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware($authRateLimit); // 3 resets par heure

    // D15 — Portail public / OPAC (dernier domaine, vague 6). Lecture publique
    // + endpoints usager connecté (auth:sanctum posé par route). Voir le fichier.
    require __DIR__ . '/api/D15.php';
});



// Secure public API routes with rate limiting
Route::prefix('public')->name('api.secure.public.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
    // Only allow the store method for users (registration only)
    Route::apiResource('users', PublicUserApiController::class)->only(['store'])->names('users');

    // Only allow safe methods for document requests
    Route::apiResource('document-requests', PublicDocumentRequestApiController::class)
        ->only(['store'])
        ->names("document-requests")
        ->middleware('rate.limit:document_request,20,60'); // 20 demandes de documents par heure

    // Only allow safe methods for responses
    Route::apiResource('responses', PublicResponseApiController::class)->only(['store'])->names("responses");
});



// MCP/AI API routes retirées

// Prompt & AI routes (use web session auth to support same-origin Blade pages)
// Add a specific rate limit for AI actions to prevent abuse
Route::middleware(['web', 'auth', 'rate.limit:ai,30,60'])->group(function () {
    Route::get('prompts', [PromptController::class, 'index'])->name('api.prompts.index');
    Route::get('prompts/{id}', [PromptController::class, 'show'])->name('api.prompts.show');
    Route::post('prompts/{id}/actions', [PromptController::class, 'actions'])->name('api.prompts.actions');
    // Apply AI suggestions to records
    Route::prefix('records/{record}/ai')->name('api.records.ai.')->group(function () {
        Route::post('title', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveTitle'])->name('title');
        Route::post('summary', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveSummary'])->name('summary');
        Route::post('thesaurus', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveThesaurus'])->name('thesaurus');
        Route::post('thesaurus/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestThesaurus'])->name('thesaurus.suggest');
        Route::post('thesaurus/auto', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'autoSuggestThesaurus'])->name('thesaurus.auto');
        Route::post('thesaurus/suggest-json', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestThesaurusFromJson'])->name('thesaurus.suggest_json');
        Route::post('activity', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveActivity'])->name('activity');
        Route::post('activity/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestActivityCandidates'])->name('activity.suggest');
        Route::post('keywords', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveKeywords'])->name('keywords');
        Route::post('keywords/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestKeywords'])->name('keywords.suggest');
    });

    // Recherche de containers pour les slips
    Route::get('containers/search', [ContainerSearchController::class, 'search'])->name('api.containers.search');
});

// Phase 9: Digital Records Management API (v1)
Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {

    // Digital Folders API
    Route::apiResource('digital-folders', RecordDigitalFolderApiController::class);
    Route::get('digital-folders-tree', [RecordDigitalFolderApiController::class, 'tree'])->name('digital-folders.tree');
    Route::get('digital-folders-roots', [RecordDigitalFolderApiController::class, 'roots'])->name('digital-folders.roots');
    Route::post('digital-folders/{id}/move', [RecordDigitalFolderApiController::class, 'move'])->name('digital-folders.move');
    Route::get('digital-folders/{id}/statistics', [RecordDigitalFolderApiController::class, 'statistics'])->name('digital-folders.statistics');
    Route::get('digital-folders/{id}/ancestors', [RecordDigitalFolderApiController::class, 'ancestors'])->name('digital-folders.ancestors');

    // Digital Documents API
    Route::apiResource('digital-documents', RecordDigitalDocumentApiController::class);
    Route::post('digital-documents/{id}/versions', [RecordDigitalDocumentApiController::class, 'createVersion'])->name('digital-documents.create-version');
    Route::post('digital-documents/{id}/submit', [RecordDigitalDocumentApiController::class, 'submitForApproval'])->name('digital-documents.submit');
    Route::post('digital-documents/{id}/approve', [RecordDigitalDocumentApiController::class, 'approve'])->name('digital-documents.approve');
    Route::post('digital-documents/{id}/reject', [RecordDigitalDocumentApiController::class, 'reject'])->name('digital-documents.reject');
    Route::get('digital-documents/{id}/download', [RecordDigitalDocumentApiController::class, 'download'])->name('digital-documents.download');
    Route::get('digital-documents/{id}/versions', [RecordDigitalDocumentApiController::class, 'versions'])->name('digital-documents.versions');

    // Digital to Physical Transfer API
    Route::prefix('record-digital-transfer')->name('record-digital-transfer.')->group(function () {
        Route::get('form', [\App\Http\Controllers\RecordDigitalTransferController::class, 'showTransferForm'])->name('form');
        Route::post('/', [\App\Http\Controllers\RecordDigitalTransferController::class, 'store'])->name('store');
        Route::delete('cancel', [\App\Http\Controllers\RecordDigitalTransferController::class, 'cancel'])->name('cancel');
    });

    // Metadata API
    Route::get('metadata/folder-types/{typeId}', [App\Http\Controllers\Api\MetadataApiController::class, 'getFolderTypeMetadata'])->name('metadata.folder-types');
    Route::get('metadata/document-types/{typeId}', [App\Http\Controllers\Api\MetadataApiController::class, 'getDocumentTypeMetadata'])->name('metadata.document-types');
    Route::get('metadata/all', [App\Http\Controllers\Api\MetadataApiController::class, 'getAllMetadata'])->name('metadata.all');
    Route::get('digital-documents-search', [RecordDigitalDocumentApiController::class, 'search'])->name('digital-documents.search');
});
