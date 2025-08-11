<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpController;


Route::prefix('admin/mcp')->middleware(['auth'])->group(function () {

    Route::get('/', [McpController::class, 'dashboard'])->name('admin.mcp.dashboard');
    Route::get('/dashboard', [McpController::class, 'dashboard'])->name('admin.mcp.dashboard.main');

    Route::get('/statistics', [McpController::class, 'statistics'])->name('admin.mcp.statistics');
    Route::get('/history', [McpController::class, 'history'])->name('admin.mcp.history');
    Route::get('/health-check', [McpController::class, 'healthCheck'])->name('admin.mcp.health-check');
    Route::get('/queue-monitor', [McpController::class, 'queueMonitor'])->name('admin.mcp.queue-monitor');
    Route::get('/logs', [McpController::class, 'logs'])->name('admin.mcp.logs');
    Route::get('/performance', [McpController::class, 'performance'])->name('admin.mcp.performance');

    Route::match(['GET', 'POST'], '/title-reformulation', [McpController::class, 'titleReformulation'])->name('admin.mcp.title-reformulation');
    Route::match(['GET', 'POST'], '/thesaurus-indexing', [McpController::class, 'thesaurusIndexing'])->name('admin.mcp.thesaurus-indexing');
    Route::match(['GET', 'POST'], '/content-summary', [McpController::class, 'contentSummary'])->name('admin.mcp.content-summary');
    Route::get('/batch-processing', [McpController::class, 'batchProcessing'])->name('admin.mcp.batch-processing');

    Route::match(['GET', 'POST'], '/configuration', [McpController::class, 'configuration'])->name('admin.mcp.configuration');
    Route::get('/models', [McpController::class, 'models'])->name('admin.mcp.models');
    Route::get('/users', [McpController::class, 'users'])->name('admin.mcp.users');





