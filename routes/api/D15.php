<?php

/*
|--------------------------------------------------------------------------
| API — D15 Portail public / OPAC (vague 6, dernier domaine)
|--------------------------------------------------------------------------
| Portage des DONNÉES du portail public sous /api/public. Guard `public`
| (modèle App\Models\PublicUser, tokens Sanctum).
|
| Inclus dans le groupe `api.public.` de routes/api.php (rate.limit
| api_general,100,60). Les écritures et les données personnelles
| (feedback, inscriptions, journaux de recherche) exigent en plus
| `auth:sanctum` : l'utilisateur est alors le PublicUser porté par le token.
|
| Les records (/api/public/records) et l'authentification usager
| (/api/public/users/*) sont déjà portés par routes/api.php : non redéfinis ici.
|
| R05 — RENDU DE TEMPLATES : EXCLUS du périmètre.
| OPAC\TemplateController rend les gabarits (substitution de variables,
| personnalisation en session). Le moteur de templates et sa sécurité
| (sandbox, liste blanche de variables, politique de rendu) ne sont PAS
| portés en API. Repli : « OPAC conservé sur Laravel » — le rendu continue
| de vivre côté Blade/session. L'API n'expose ici que les DONNÉES des
| templates (contenu brut + variables + paramètres), sans jamais rendre.
| Le frontend consommant ces données doit appliquer son propre moteur
| (échappement systématique, aucune exécution de code côté client).
*/

use App\Http\Controllers\Api\Public\EventController;
use App\Http\Controllers\Api\Public\FeedbackController;
use App\Http\Controllers\Api\Public\NewsController;
use App\Http\Controllers\Api\Public\PageController;
use App\Http\Controllers\Api\Public\SearchLogController;
use App\Http\Controllers\Api\Public\TemplateController;
use Illuminate\Support\Facades\Route;

/*
 * News — lecture publique. `latest` doit précéder l'apiResource : sinon le
 * paramètre `{news}` capterait le mot « latest ».
 */
Route::get('news/latest', [NewsController::class, 'latest'])->name('news.latest');
Route::apiResource('news', NewsController::class)->only(['index', 'show']);

/*
 * Événements — lecture publique ; inscription réservée aux usagers connectés
 * (guard public). Les registrations exposent le statut de l'usager courant,
 * jamais la liste nominative des participants.
 */
Route::apiResource('events', EventController::class)->only(['index', 'show']);
Route::post('events/{event}/registrations', [EventController::class, 'register'])
    ->middleware('auth:sanctum')->name('events.registrations.store');
Route::get('events/{event}/registrations', [EventController::class, 'registration'])
    ->middleware('auth:sanctum')->name('events.registrations.show');
Route::delete('events/{event}/registrations', [EventController::class, 'cancelRegistration'])
    ->middleware('auth:sanctum')->name('events.registrations.destroy');

/*
 * Pages — lecture publique (uniquement publiées).
 */
Route::apiResource('pages', PageController::class)->only(['index', 'show']);

/*
 * Templates — DONNÉES uniquement (voir R05 en tête de fichier).
 */
Route::apiResource('templates', TemplateController::class)->only(['index', 'show']);

/*
 * Feedback — soumission et historique personnels (usager public connecté).
 * `user_id` est déduit du token, jamais fourni par le client.
 */
Route::get('feedbacks', [FeedbackController::class, 'index'])
    ->middleware('auth:sanctum')->name('feedbacks.index');
Route::post('feedbacks', [FeedbackController::class, 'store'])
    ->middleware('auth:sanctum')->name('feedbacks.store');

/*
 * Journaux de recherche — propriété de l'usager connecté.
 */
Route::get('search-logs', [SearchLogController::class, 'index'])
    ->middleware('auth:sanctum')->name('search-logs.index');
Route::post('search-logs', [SearchLogController::class, 'store'])
    ->middleware('auth:sanctum')->name('search-logs.store');
Route::delete('search-logs', [SearchLogController::class, 'clear'])
    ->middleware('auth:sanctum')->name('search-logs.clear');
Route::delete('search-logs/{searchLog}', [SearchLogController::class, 'destroy'])
    ->middleware('auth:sanctum')->name('search-logs.destroy');
