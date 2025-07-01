<?php

// Routes pour le module Workflow
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowTemplateController;
use App\Http\Controllers\WorkflowStepController;
use App\Http\Controllers\WorkflowInstanceController;
use App\Http\Controllers\WorkflowStepInstanceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemNotificationController;

Route::middleware(['auth'])->prefix('workflow')->name('workflow.')->group(function () {
    // Routes pour les templates de workflow
    Route::resource('templates', WorkflowTemplateController::class);
    Route::post('templates/{template}/toggle-active', [WorkflowTemplateController::class, 'toggleActive'])->name('templates.toggle-active');
    Route::post('templates/{template}/duplicate', [WorkflowTemplateController::class, 'duplicate'])->name('templates.duplicate');

    // Routes pour les étapes de workflow
    Route::resource('templates.steps', WorkflowStepController::class)->shallow();
    Route::post('steps/{step}/assignments', [WorkflowStepController::class, 'storeAssignments'])->name('steps.assignments.store');
    Route::delete('steps/{step}/assignments/{assignment}', [WorkflowStepController::class, 'destroyAssignment'])->name('steps.assignments.destroy');
    Route::post('templates/{template}/steps/reorder', [WorkflowStepController::class, 'reorder'])->name('templates.steps.reorder');

    // Routes pour les instances de workflow
    Route::resource('instances', WorkflowInstanceController::class);
    Route::post('instances/{instance}/start', [WorkflowInstanceController::class, 'start'])->name('instances.start');
    Route::post('instances/{instance}/cancel', [WorkflowInstanceController::class, 'cancel'])->name('instances.cancel');
    Route::post('instances/{instance}/pause', [WorkflowInstanceController::class, 'pause'])->name('instances.pause');
    Route::post('instances/{instance}/resume', [WorkflowInstanceController::class, 'resume'])->name('instances.resume');

    // Routes pour les instances d'étapes de workflow
    Route::resource('step-instances', WorkflowStepInstanceController::class)->only(['show', 'update']);
    Route::post('step-instances/{stepInstance}/complete', [WorkflowStepInstanceController::class, 'complete'])->name('step-instances.complete');
    Route::post('step-instances/{stepInstance}/reject', [WorkflowStepInstanceController::class, 'reject'])->name('step-instances.reject');
    Route::post('step-instances/{stepInstance}/reassign', [WorkflowStepInstanceController::class, 'reassign'])->name('step-instances.reassign');

    // Dashboard du module workflow
    Route::get('/', function () {
        return redirect()->route('workflow.dashboard');
    });
    Route::get('dashboard', [WorkflowInstanceController::class, 'dashboard'])->name('dashboard');
});

// Routes pour les tâches liées au workflow
Route::middleware(['auth'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::get('/my', [TaskController::class, 'myTasks'])->name('my');
    Route::get('/create', [TaskController::class, 'create'])->name('create');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

    // Commentaires sur les tâches
    Route::post('{task}/comments', [TaskCommentController::class, 'store'])->name('comments.store');
    Route::delete('{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('comments.destroy');

    // Assignation de tâches
    Route::post('{task}/assignments', [TaskAssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('{task}/assignments/{assignment}', [TaskAssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Actions spéciales sur les tâches
    Route::post('{task}/complete', [TaskController::class, 'complete'])->name('complete');
    Route::post('{task}/start', [TaskController::class, 'start'])->name('start');
    Route::post('{task}/pause', [TaskController::class, 'pause'])->name('pause');
});

// Routes pour les notifications du système
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::get('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');

    Route::resource('system', SystemNotificationController::class)->only(['index', 'show', 'update']);
    Route::post('subscribe/{channel}/{entity_type}/{entity_id}', [NotificationController::class, 'subscribe'])->name('subscribe');
    Route::post('unsubscribe/{subscription}', [NotificationController::class, 'unsubscribe'])->name('unsubscribe');
});
