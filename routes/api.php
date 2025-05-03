<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicUserController;
use App\Http\Controllers\Api\PublicEventController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\PublicPageController;
use App\Http\Controllers\Api\PublicDocumentRequestController;
use App\Http\Controllers\Api\PublicFeedbackController;
use App\Http\Controllers\Api\PublicSearchLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Portal API v1
Route::prefix('v1/portal')->middleware(['auth:sanctum'])->group(function () {
    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [PublicUserController::class, 'index']);
        Route::post('/', [PublicUserController::class, 'store']);
        Route::get('/{user}', [PublicUserController::class, 'show']);
        Route::put('/{user}', [PublicUserController::class, 'update']);
        Route::delete('/{user}', [PublicUserController::class, 'destroy']);
        Route::post('/{user}/approve', [PublicUserController::class, 'approve']);
        Route::post('/{user}/reject', [PublicUserController::class, 'reject']);
    });

    // Events
    Route::prefix('events')->group(function () {
        Route::get('/', [PublicEventController::class, 'index']);
        Route::post('/', [PublicEventController::class, 'store']);
        Route::get('/upcoming', [PublicEventController::class, 'upcoming']);
        Route::get('/past', [PublicEventController::class, 'past']);
        Route::get('/search', [PublicEventController::class, 'search']);
        Route::get('/{event}', [PublicEventController::class, 'show']);
        Route::put('/{event}', [PublicEventController::class, 'update']);
        Route::delete('/{event}', [PublicEventController::class, 'destroy']);
    });

    // News
    Route::prefix('news')->group(function () {
        Route::get('/', [PublicNewsController::class, 'index']);
        Route::post('/', [PublicNewsController::class, 'store']);
        Route::get('/latest', [PublicNewsController::class, 'latest']);
        Route::get('/category/{category}', [PublicNewsController::class, 'category']);
        Route::get('/search', [PublicNewsController::class, 'search']);
        Route::get('/{news}', [PublicNewsController::class, 'show']);
        Route::put('/{news}', [PublicNewsController::class, 'update']);
        Route::delete('/{news}', [PublicNewsController::class, 'destroy']);
    });

    // Pages
    Route::prefix('pages')->group(function () {
        Route::get('/', [PublicPageController::class, 'index']);
        Route::post('/', [PublicPageController::class, 'store']);
        Route::get('/category/{category}', [PublicPageController::class, 'category']);
        Route::get('/search', [PublicPageController::class, 'search']);
        Route::get('/sitemap', [PublicPageController::class, 'sitemap']);
        Route::get('/{page}', [PublicPageController::class, 'show']);
        Route::put('/{page}', [PublicPageController::class, 'update']);
        Route::delete('/{page}', [PublicPageController::class, 'destroy']);
        Route::get('/slug/{slug}', [PublicPageController::class, 'bySlug']);
    });

    // Document Requests
    Route::prefix('document-requests')->group(function () {
        Route::get('/', [PublicDocumentRequestController::class, 'index']);
        Route::post('/', [PublicDocumentRequestController::class, 'store']);
        Route::get('/{request}', [PublicDocumentRequestController::class, 'show']);
        Route::put('/{request}', [PublicDocumentRequestController::class, 'update']);
        Route::delete('/{request}', [PublicDocumentRequestController::class, 'destroy']);
        Route::post('/{request}/cancel', [PublicDocumentRequestController::class, 'cancel']);
        Route::post('/{request}/respond', [PublicDocumentRequestController::class, 'respond']);
    });

    // Feedback
    Route::prefix('feedback')->group(function () {
        Route::get('/', [PublicFeedbackController::class, 'index']);
        Route::post('/', [PublicFeedbackController::class, 'store']);
        Route::get('/{feedback}', [PublicFeedbackController::class, 'show']);
        Route::put('/{feedback}', [PublicFeedbackController::class, 'update']);
        Route::delete('/{feedback}', [PublicFeedbackController::class, 'destroy']);
        Route::post('/{feedback}/respond', [PublicFeedbackController::class, 'respond']);
    });

    // Search Logs
    Route::prefix('search-logs')->group(function () {
        Route::get('/', [PublicSearchLogController::class, 'index']);
        Route::get('/search', [PublicSearchLogController::class, 'search']);
        Route::get('/analytics', [PublicSearchLogController::class, 'analytics']);
        Route::get('/{log}', [PublicSearchLogController::class, 'show']);
    });
});

// Portal routes (accessible to all authenticated users)
Route::prefix('portal')->middleware(['auth'])->group(function () {
    // Events
    Route::get('events', [PortalEventController::class, 'index'])->name('portal.events.index');
    Route::get('events/{event}', [PortalEventController::class, 'show'])->name('portal.events.show');
    Route::post('events/{event}/register', [PortalEventController::class, 'register'])->name('portal.events.register');

    // News
    Route::get('news', [PortalNewsController::class, 'index'])->name('portal.news.index');
    Route::get('news/{news}', [PortalNewsController::class, 'show'])->name('portal.news.show');

    // Pages
    Route::get('pages', [PortalPageController::class, 'index'])->name('portal.pages.index');
    Route::get('pages/{page}', [PortalPageController::class, 'show'])->name('portal.pages.show');
    Route::get('pages/slug/{slug}', [PortalPageController::class, 'bySlug'])->name('portal.pages.by-slug');

    // Document Requests
    Route::get('document-requests', [PublicDocumentRequestController::class, 'publicIndex'])->name('portal.document-requests.index');
    Route::get('document-requests/create', [PublicDocumentRequestController::class, 'create'])->name('portal.document-requests.create');
    Route::post('document-requests', [PublicDocumentRequestController::class, 'store'])->name('portal.document-requests.store');
    Route::get('document-requests/{request}', [PublicDocumentRequestController::class, 'show'])->name('portal.document-requests.show');

    // Records
    Route::get('records', [PublicRecordController::class, 'publicIndex'])->name('portal.records.index');
    Route::get('records/{record}', [PublicRecordController::class, 'publicShow'])->name('portal.records.show');

    // Feedback
    Route::get('feedback/create', [PublicFeedbackController::class, 'create'])->name('portal.feedback.create');
    Route::post('feedback', [PublicFeedbackController::class, 'store'])->name('portal.feedback.store');
});
