<?php

// MCP/AI routes retirées

use App\Http\Controllers\Admin\OpacConfigurationController;
use App\Http\Controllers\Admin\PublicUserController as AdminPublicUserController;
use App\Http\Controllers\BulletinBoardAdminController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PhantomController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RateLimitController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MailSendController;
use App\Http\Controllers\MailReceivedController;
use App\Http\Controllers\MailArchiveController;
use App\Http\Controllers\MailSendExternalController;
use App\Http\Controllers\MailReceivedExternalController;

use App\Http\Controllers\MailAttachmentController;
use App\Http\Controllers\MailContainerController;
use App\Http\Controllers\MailContainerTransferController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchReceivedController;
use App\Http\Controllers\BatchSendController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\RecordAuthorController;
use App\Http\Controllers\RecordAttachmentController;
use App\Http\Controllers\RecordContainerController;
use App\Http\Controllers\activityCommunicabilityController;
use App\Http\Controllers\MailAuthorController;
use App\Http\Controllers\MailTransactionController;
use App\Http\Controllers\MailAuthorContactController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\floorController;
use App\Http\Controllers\ExternalContactController;
use App\Http\Controllers\ExternalOrganizationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AuthorContactController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\MailTypologyController;
use App\Http\Controllers\ContainerStatusController;
use App\Http\Controllers\ContainerPropertyController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SortController;
use App\Http\Controllers\RetentionController;
use App\Http\Controllers\LawController;
use App\Http\Controllers\LawArticleController;
use App\Http\Controllers\RetentionLawArticleController;
use App\Http\Controllers\retentionActivityController;
use App\Http\Controllers\CommunicabilityController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationRoomController;
use App\Http\Controllers\OrganisationActivityController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ThesaurusAssociativeRelationController;
use App\Http\Controllers\ThesaurusTranslationController;
use App\Http\Controllers\ThesaurusController;
use App\Http\Controllers\ThesaurusSchemeController;
use App\Http\Controllers\BatchTransferController;
use App\Http\Controllers\ThesaurusSearchController;
use App\Http\Controllers\ThesaurusExportImportController;
use App\Http\Controllers\PublicSearchLogController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RecordDragDropController;
use App\Http\Controllers\LifeCycleController;
use App\Http\Controllers\RecordChildController;
use App\Http\Controllers\RecordSupportController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\SearchCommunicationController;
use App\Http\Controllers\CommunicationRecordController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationRecordController;
use App\Http\Controllers\RecordStatusController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchMailController;
use App\Http\Controllers\SearchReservationController;
use App\Http\Controllers\SearchdollyController;
use App\Http\Controllers\SearchRecordController;
use App\Http\Controllers\BatchHandlerController;
use App\Http\Controllers\MailPriorityController;
use App\Http\Controllers\DollyController;
use App\Http\Controllers\DollyHandlerController;
use App\Http\Controllers\DollyMailTransactionController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SlipStatusController;
use App\Http\Controllers\SlipRecordController;
use App\Http\Controllers\SlipRecordAttachmentController;
use App\Http\Controllers\SlipController;
use App\Http\Controllers\SlipContainerController;
use App\Http\Controllers\SlipRecordContainerController;
use App\Http\Controllers\MailActionController;
use App\Http\Controllers\MailWorkflowController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOrganisationRoleController;
use App\Http\Controllers\DollyActionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SearchMailFeedbackController;
use App\Http\Controllers\SearchSlipController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BulletinBoardController;
use App\Http\Controllers\EventAttachmentController;
use App\Http\Controllers\PostAttachmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupFileController;
use App\Http\Controllers\BackupPlanningController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingCategoryController;
use App\Http\Controllers\SEDAExportController;
use App\Http\Controllers\PromptManagementController;

use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\PublicChatController;
use App\Http\Controllers\PublicChatMessageController;
use App\Http\Controllers\PublicChatParticipantController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicEventRegistrationController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicTemplateController;
use App\Http\Controllers\PublicDocumentRequestController;
use App\Http\Controllers\PublicRecordController;
use App\Http\Controllers\PublicResponseController;
use App\Http\Controllers\PublicResponseAttachmentController;
use App\Http\Controllers\PublicFeedbackController;
use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Gate;

// MCP retiré
use App\Http\Controllers\RecordEnricherController;


Route::get('/', function () {
        return redirect('/dashboard');
});

Auth::routes();

// Dashboard route
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('pdf/thumbnail/{id}', [PDFController::class, 'thumbnail'])->name('pdf.thumbnail');

Route::group(['middleware' => 'auth'], function () {
    Route::prefix('api')->group(function () {
        Route::get('/authors', [AuthorController::class, 'indexApi']);
        Route::post('/authors', [AuthorController::class, 'storeApi']);
        Route::get('/author-types', [AuthorController::class, 'authorTypesApi']);
        Route::get('/organisations', function() {
            return \App\Models\Organisation::orderBy('name')->get(['id', 'name']);
        });
        Route::get('/organisations/{organisation}/users', function(\App\Models\Organisation $organisation) {
            return $organisation->users()->orderBy('name')->get(['id', 'name', 'email']);
        });
        Route::get('/records/search', [\App\Http\Controllers\Api\RecordSearchController::class, 'search'])->name('api.records.search');
        Route::get('/containers', function() {
            $q = request('q');
            $orgId = Auth::user()->current_organisation_id ?? null;
            $query = \App\Models\Container::query()
                ->whereHas('shelf.room.organisations', function($q2) use ($orgId) {
                    if ($orgId) {
                        $q2->where('organisations.id', $orgId);
                    } else {
                        // Si pas d'organisation courante, retourner zéro résultat pour éviter fuite
                        $q2->whereRaw('1=0');
                    }
                });
            if ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('code', 'like', "%$q%")
                        ->orWhere('name', 'like', "%$q%");
                });
            }
            return $query->with(['shelf:id,code','shelf.room:id,code'])
                ->orderBy('code')
                ->limit(50)
                ->get(['id','code','name','shelve_id']);
        })->name('api.containers');
    });

    Route::post('/switch-organisation', [OrganisationController::class, 'switchOrganisation'])->name('switch.organisation');
    Route::get('/', [MailReceivedController::class, 'index'])->name('home');

    // Admin Panel (Phase 10 - Task 10.7)
    // TODO: Implement AdminPanelController
    /*
    Route::prefix('admin-panel')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Web\AdminPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('users', [\App\Http\Controllers\Web\AdminPanelController::class, 'users'])->name('users');
        Route::get('settings', [\App\Http\Controllers\Web\AdminPanelController::class, 'settings'])->name('settings');
        Route::get('logs', [\App\Http\Controllers\Web\AdminPanelController::class, 'logs'])->name('logs');
    });
    */

    // Routes avec authentification pour les bulletin boards
    Route::middleware(['auth'])->prefix('bulletin-boards')->group(function () {
        // Routes principales des bulletin boards
        Route::get('/', [BulletinBoardController::class, 'index'])->name('bulletin-boards.index');
        Route::get('/create', [BulletinBoardController::class, 'create'])->name('bulletin-boards.create');
        Route::post('/', [BulletinBoardController::class, 'store'])->name('bulletin-boards.store');
        Route::get('/{bulletinBoard}', [BulletinBoardController::class, 'show'])->name('bulletin-boards.show');
        Route::get('/{bulletinBoard}/edit', [BulletinBoardController::class, 'edit'])->name('bulletin-boards.edit');
        Route::put('/{bulletinBoard}', [BulletinBoardController::class, 'update'])->name('bulletin-boards.update');
        Route::delete('/{bulletinBoard}', [BulletinBoardController::class, 'destroy'])->name('bulletin-boards.destroy');

        // Routes additionnelles des bulletin boards
        Route::get('/dashboard', [BulletinBoardController::class, 'dashboard'])->name('bulletin-boards.dashboard');
        Route::get('/my-posts', [BulletinBoardController::class, 'myPosts'])->name('bulletin-boards.my-posts');
        Route::get('/archives', [BulletinBoardController::class, 'archives'])->name('bulletin-boards.archives');
        Route::post('/{bulletinBoard}/archive', [BulletinBoardController::class, 'toggleArchive'])->name('bulletin-boards.toggle-archive');

        // Routes pour les organisations
        Route::prefix('/organisations')->name('organisations.')->group(function () {
            Route::post('/{bulletinBoard}/attach', [BulletinBoardController::class, 'attachOrganisation'])->name('bulletin-boards.attach');
            Route::delete('/{bulletinBoard}/detach/{organisation}', [BulletinBoardController::class, 'detachOrganisation'])->name('bulletin-boards.detach');
        });

        // Routes pour les Events
        Route::get('/{bulletinBoard}/events', [EventController::class, 'index'])->name('bulletin-boards.events.index');
        Route::get('/{bulletinBoard}/events/create', [EventController::class, 'create'])->name('bulletin-boards.events.create');
        Route::post('/{bulletinBoard}/events', [EventController::class, 'store'])->name('bulletin-boards.events.store');
        Route::get('/{bulletinBoard}/events/{event}', [EventController::class, 'show'])->name('bulletin-boards.events.show');
        Route::get('/{bulletinBoard}/events/{event}/edit', [EventController::class, 'edit'])->name('bulletin-boards.events.edit');
        Route::put('/{bulletinBoard}/events/{event}', [EventController::class, 'update'])->name('bulletin-boards.events.update');
        Route::delete('/{bulletinBoard}/events/{event}', [EventController::class, 'destroy'])->name('bulletin-boards.events.destroy');
        Route::post('/{bulletinBoard}/events/{event}/update-status', [EventController::class, 'updateStatus'])->name('bulletin-boards.events.update-status');
        Route::post('/{bulletinBoard}/events/{event}/register', [EventController::class, 'register'])->name('bulletin-boards.events.register');
        Route::post('/{bulletinBoard}/events/{event}/unregister', [EventController::class, 'unregister'])->name('bulletin-boards.events.unregister');

        // Routes pour les pièces jointes des Events
        Route::get('/{bulletinBoard}/events/{event}/attachments', [EventController::class, 'attachmentsIndex'])->name('bulletin-boards.events.attachments.index');
        Route::get('/{bulletinBoard}/events/{event}/attachments/create', [EventController::class, 'attachmentsCreate'])->name('bulletin-boards.events.attachments.create');
        Route::post('/{bulletinBoard}/events/{event}/attachments', [EventController::class, 'attachmentsStore'])->name('bulletin-boards.events.attachments.store');
        Route::get('/{bulletinBoard}/events/{event}/attachments/{attachment}', [EventController::class, 'attachmentsShow'])->name('bulletin-boards.events.attachments.show');
        Route::delete('/{bulletinBoard}/events/{event}/attachments/{attachment}', [EventController::class, 'attachmentsDestroy'])->name('bulletin-boards.events.attachments.destroy');
        Route::get('/events/{id}/preview', [EventController::class, 'attachmentsPreview'])->name('events.attachments.preview');
        Route::get('/events/{id}/download', [EventController::class, 'attachmentsDownload'])->name('events.attachments.download');
        Route::get('/{bulletinBoard}/events/{event}/attachments/list', [EventController::class, 'attachmentsList'])->name('bulletin-boards.events.attachments.list');
        Route::post('/{bulletinBoard}/events/{event}/attachments/ajax', [EventController::class, 'attachmentsAjaxStore'])->name('bulletin-boards.events.attachments.ajax.store');
        Route::delete('/{bulletinBoard}/events/{event}/attachments/{attachment}/ajax', [EventController::class, 'attachmentsAjaxDestroy'])->name('bulletin-boards.events.attachments.ajax.destroy');

    // Notifications retirées

        // Routes principales pour les publications (existantes)
        Route::get('/{bulletinBoard}/posts', [PostController::class, 'index'])
            ->name('bulletin-boards.posts.index');
        Route::get('/{bulletinBoard}/posts/create', [PostController::class, 'create'])
            ->name('bulletin-boards.posts.create');
        Route::post('/{bulletinBoard}/posts', [PostController::class, 'store'])
            ->name('bulletin-boards.posts.store');
        Route::get('/{bulletinBoard}/posts/{post}', [PostController::class, 'show'])
            ->name('bulletin-boards.posts.show');
        Route::get('/{bulletinBoard}/posts/{post}/edit', [PostController::class, 'edit'])
            ->name('bulletin-boards.posts.edit');
        Route::put('/{bulletinBoard}/posts/{post}', [PostController::class, 'update'])
            ->name('bulletin-boards.posts.update');
        Route::delete('/{bulletinBoard}/posts/{post}', [PostController::class, 'destroy'])
            ->name('bulletin-boards.posts.destroy');
        Route::post('/{bulletinBoard}/posts/{post}/toggle-status', [PostController::class, 'toggleStatus'])
            ->name('bulletin-boards.posts.toggle-status');
        Route::post('/{bulletinBoard}/posts/{post}/cancel', [PostController::class, 'cancel'])
            ->name('bulletin-boards.posts.cancel');

        // Routes pour les pièces jointes des publications (existantes)
        Route::get('/{bulletinBoard}/posts/{post}/attachments', [PostController::class, 'attachmentsIndex'])
            ->name('bulletin-boards.posts.attachments.index');
        Route::get('/{bulletinBoard}/posts/{post}/attachments/create', [PostController::class, 'attachmentsCreate'])
            ->name('bulletin-boards.posts.attachments.create');
        Route::post('/{bulletinBoard}/posts/{post}/attachments', [PostController::class, 'attachmentsStore'])
            ->name('bulletin-boards.posts.attachments.store');
        Route::get('/{bulletinBoard}/posts/{post}/attachments/{attachment}', [PostController::class, 'attachmentsShow'])
            ->name('bulletin-boards.posts.attachments.show');
        Route::delete('/{bulletinBoard}/posts/{post}/attachments/{attachment}', [PostController::class, 'attachmentsDestroy'])
            ->name('bulletin-boards.posts.attachments.destroy');

        // Routes utilitaires pour les pièces jointes des publications (existantes)
        Route::get('/posts/attachments/{attachment}/preview', [PostController::class, 'attachmentsPreview'])
            ->name('posts.attachments.preview');
        Route::get('/posts/attachments/{attachment}/download', [PostController::class, 'attachmentsDownload'])
            ->name('posts.attachments.download');

        // Routes AJAX pour les pièces jointes des publications (existantes)
        Route::get('/{bulletinBoard}/posts/{post}/attachments/list', [PostController::class, 'attachmentsList'])
            ->name('bulletin-boards.posts.attachments.list');
        Route::post('/{bulletinBoard}/posts/{post}/attachments/ajax', [PostController::class, 'attachmentsAjaxStore'])
            ->name('bulletin-boards.posts.attachments.ajax.store');
        Route::delete('/{bulletinBoard}/posts/{post}/attachments/{attachment}/ajax', [PostController::class, 'attachmentsAjaxDestroy'])
            ->name('bulletin-boards.posts.attachments.ajax.destroy');
    });






    // Routes utilitaires pour les pièces jointes générales
    Route::middleware(['auth'])->group(function () {
        Route::get('/attachments/{attachment}/preview', [EventController::class, 'attachmentsPreview'])->name('attachments.preview');
        Route::get('/attachments/{attachment}/download', [EventController::class, 'attachmentsDownload'])->name('attachments.download');
    });


    Route::prefix('mails')->middleware(['auth'])->group(function () {
        // New route for searching mails
        Route::get('/search', [\App\Http\Controllers\MailController::class, 'apiSearch'])->name('api.mails.search');


        Route::resource('container', MailContainerController::class)->names('mail-container');
        Route::get('containers/list', [MailContainerController::class, 'getContainers'])->name('mail-container.list');
        Route::get('containers/properties', [MailContainerController::class, 'getContainerProperties'])->name('mail-container.properties');
        Route::post('containers/transfer', [MailContainerTransferController::class, 'transfer'])->name('mail-container.transfer');
        Route::get('containers/shelves/{organisation}', [MailContainerTransferController::class, 'getShelvesByOrganisation'])->name('mail-container.shelves');
        Route::get('containers/activities/{organisation}', [MailContainerTransferController::class, 'getActivitiesByOrganisation'])->name('mail-container.activities');

        // Routes pour les courriers entrants externes (spécifiques en premier)
        Route::prefix('received/external')->name('mails.received.external.')->group(function () {
            Route::get('/', [MailReceivedExternalController::class, 'index'])->name('index');
            Route::get('/create', [MailReceivedExternalController::class, 'create'])->name('create');
            Route::post('/', [MailReceivedExternalController::class, 'store'])->name('store');
            Route::get('/{id}', [MailReceivedExternalController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [MailReceivedExternalController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MailReceivedExternalController::class, 'update'])->name('update');
            Route::patch('/{id}', [MailReceivedExternalController::class, 'update']);
            Route::delete('/{id}', [MailReceivedExternalController::class, 'destroy'])->name('destroy');
        });

        // Routes pour les courriers sortants externes (spécifiques en premier)
        Route::prefix('send/external')->name('mails.send.external.')->group(function () {
            Route::get('/', [MailSendExternalController::class, 'index'])->name('index');
            Route::get('/create', [MailSendExternalController::class, 'create'])->name('create');
            Route::post('/', [MailSendExternalController::class, 'store'])->name('store');
            Route::get('/{id}', [MailSendExternalController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [MailSendExternalController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MailSendExternalController::class, 'update'])->name('update');
            Route::patch('/{id}', [MailSendExternalController::class, 'update']);
            Route::delete('/{id}', [MailSendExternalController::class, 'destroy'])->name('destroy');
        });

        // Routes génériques (après les routes spécifiques)
        Route::resource('send', MailSendController::class)->names('mail-send');
        Route::post('send/transfer', [MailSendController::class, 'transfer'])->name('mail-send.transfer');

        // Routes anciennes (compatibilité temporaire)
            Route::get('incoming', [MailController::class, 'indexIncoming'])->name('mails.incoming.index');
    Route::get('incoming/create', [MailController::class, 'createIncoming'])->name('mails.incoming.create');
    Route::get('count-unread', [MailController::class, 'countUnread'])->name('mails.count-unread');
        Route::post('incoming', [MailController::class, 'storeIncoming'])->name('mails.incoming.store');
        Route::get('incoming/{id}', [MailController::class, 'show'])->name('mails.incoming.show');
        Route::get('incoming/{id}/edit', [MailController::class, 'edit'])->name('mails.incoming.edit');
        Route::put('incoming/{id}', [MailController::class, 'update'])->name('mails.incoming.update');
        Route::patch('incoming/{id}', [MailController::class, 'update']);
        Route::delete('incoming/{id}', [MailController::class, 'destroy'])->name('mails.incoming.destroy');

        Route::get('outgoing', [MailController::class, 'indexOutgoing'])->name('mails.outgoing.index');
        Route::get('outgoing/create', [MailController::class, 'createOutgoing'])->name('mails.outgoing.create');
        Route::post('outgoing', [MailController::class, 'storeOutgoing'])->name('mails.outgoing.store');
        Route::get('outgoing/{id}', [MailController::class, 'show'])->name('mails.outgoing.show');
        Route::get('outgoing/{id}/edit', [MailController::class, 'edit'])->name('mails.outgoing.edit');
        Route::put('outgoing/{id}', [MailController::class, 'update'])->name('mails.outgoing.update');
        Route::patch('outgoing/{id}', [MailController::class, 'update']);
        Route::delete('outgoing/{id}', [MailController::class, 'destroy'])->name('mails.outgoing.destroy');

        // Route pour le résumé AI des mails
        Route::get('{mail}/summarize', [\App\Http\Controllers\Api\AiMailController::class, 'summarize'])->name('mail.summarize');
        Route::post('{mail}/save-summary', [\App\Http\Controllers\Api\AiMailController::class, 'saveSummary'])->name('mail.saveSummary');

        Route::resource('received', MailReceivedController::class)->names('mail-received');

        Route::get('received/{mail}/approve', [MailReceivedController::class, 'approve'])->name('mail-received.approve');
        Route::get('received/{mail}/reject', [MailReceivedController::class, 'reject'])->name('mail-received.reject');

        // Route pour les courriers retournés
        Route::get('returned', [MailReceivedController::class, 'returned'])->name('mail-received.returned');

        // Route pour les courriers à retourner (filtre spécifique)
        Route::get('to-return', [MailReceivedController::class, 'toReturn'])->name('mail-received.to-return');

        // Route pour les courriers à retourner
        Route::get('to-return', [MailReceivedController::class, 'toReturn'])->name('mail-received.toReturn');
        // Route pour les courriers à retourner

        Route::get('feedback', [SearchMailFeedbackController::class, 'index'])->name('mail-feedback');
        Route::get('/organisations/{organisation}/users', function(\App\Models\Organisation $organisation) {
            return $organisation->users;
        });

        Route::get('/organisations/list', function() {
            return App\Models\Organisation::all();
        });

        Route::resource('file.attachment', MailAttachmentController::class)->names('mail-attachment');
        Route::get('archived', [MailArchiveController::class, 'archived'])->name('mails.archived');
        Route::resource('batch', BatchController::class)->names('batch');

        // Routes pour la gestion des mails dans les batches
        Route::get('batches/{batch}/mail', [BatchController::class, 'indexMail'])->name('batch.mail.index');
        Route::get('batches/{batch}/mail/create', [BatchController::class, 'createMail'])->name('batch.mail.create');
        Route::post('batches/{batch}/mail', [BatchController::class, 'storeMail'])->name('batch.mail.store');
        Route::get('batches/{batch}/mail/{batchMail}/edit', [BatchController::class, 'editMail'])->name('batch.mail.edit');
        Route::put('batches/{batch}/mail/{batchMail}', [BatchController::class, 'updateMail'])->name('batch.mail.update');
        Route::delete('batches/{batch}/mail/{id}', [BatchController::class, 'destroyMail'])->name('batch.mail.destroy');
        Route::get('batch/{batch}/available-mails', [BatchController::class, 'getAvailableMails'])->name('batch.available-mails');

        // Transferts des courriers (Lot 2)
        Route::post('batches/{batch}/transfer/boxes', [BatchTransferController::class, 'transferToBoxes'])
            ->name('batch.transfer.boxes');
        Route::post('batches/{batch}/transfer/dollies', [BatchTransferController::class, 'transferToDollies'])
            ->name('batch.transfer.dollies');

        Route::resource('batch-received', BatchReceivedController::class)->names('batch-received');
        Route::resource('batch-send', BatchSendController::class)->names('batch-send');
        Route::get('batch/{batch}/export/pdf', [BatchController::class, 'exportPdf'])->name('batch.export.pdf');



        Route::get('batch-received/logs', [BatchReceivedController::class, 'logs'] )->name('batch-received-log');
        Route::get('batch-send/logs', [BatchSendController::class, 'logs'] )->name('batch-send-log');
        Route::post('mail-transaction/export', [MailTransactionController::class, 'export'])->name('mail-transaction.export');
        Route::post('mail-transaction/import', [MailTransactionController::class, 'import'])->name('mail-transaction.import');
        Route::post('mail-transaction/print', [MailTransactionController::class, 'print'])->name('mail-transaction.print');
        Route::post('mails/archive', [MailTransactionController::class, 'archive'])->name('mail-transaction.archive');

        /*

            Recherche du module mail

        */
        Route::post('advanced', [SearchMailController::class, 'advanced'])->name('mails.advanced');
        Route::get('mail-typologies', [SearchMailController::class, 'mailTypologies'])->name('mail-select-typologies');
        Route::get('advanced/form', [SearchMailController::class, 'form'])->name('mails.advanced.form');
        Route::get('search', [SearchController::class, 'index'])->name('mails.search');
        Route::get('sort', [SearchMailController::class, 'advanced'])->name('mails.sort');
        Route::get('select', [SearchMailController::class, 'date'])->name('mail-select-date');
        Route::get('InProgress', [MailReceivedController::class, 'inprogress'])->name('mails.inprogress');
        Route::get('feedback', [SearchMailFeedbackController::class, 'index'])->name('mails.feedback');
        Route::get('/mail-attachment/{id}/preview', [MailAttachmentController::class, 'preview'])->name('mail-attachment.preview');
        Route::get('chart', [SearchMailController::class, 'chart'])->name('mails.chart');
        Route::resource('archives', MailArchiveController::class)->names('mail-archive');
        Route::post('archives/{containerId}/add-mails', [MailArchiveController::class, 'addMails'])->name('mail-archive.add-mails');
        Route::post('archives/{containerId}/remove-mails', [MailArchiveController::class, 'removeMails'])->name('mail-archive.remove-mails');

        // Les routes pour les tâches et workflows ont été supprimées

    });

    // Routes pour les contacts et organisations externes
    Route::prefix('external')->name('external.')->group(function() {
        // Routes pour les organisations externes
        Route::resource('organizations', ExternalOrganizationController::class);

        // Routes pour les contacts externes
        Route::resource('contacts', ExternalContactController::class);
    });

    Route::get('/api/dollies', [DollyController::class, 'apiList']);
    Route::post('/api/dollies', [DollyController::class, 'apiCreate']);


    // Gestion des chariots en AJAX, les routes
            // Routes dolly-handler avec authentification
        Route::middleware(['auth'])->group(function () {
            Route::post('/dolly-handler/create', [DollyHandlerController::class, 'addDolly']);
            Route::get('/dolly-handler/list', [DollyHandlerController::class, 'list']);
            Route::post('/dolly-handler/add-items', [DollyHandlerController::class, 'addItems']);
            Route::delete('/dolly-handler/remove-items', [DollyHandlerController::class, 'removeItems']);
            Route::delete('/dolly-handler/clean', [DollyHandlerController::class, 'clean']);
            Route::delete('/dolly-handler/{dolly_id}', [DollyHandlerController::class, 'deleteDolly']);
        });

            // Gestion des parapheurs en AJAX, les routes
            // Routes batch-handler avec authentification
        Route::middleware(['auth'])->group(function () {
            Route::post('/batch-handler/create', [BatchHandlerController::class, 'create']);
            Route::get('/batch-handler/list', [BatchHandlerController::class, 'list']);
            Route::post('/batch-handler/add-items', [BatchHandlerController::class, 'addItems']);
            Route::delete('/batch-handler/remove-items', [BatchHandlerController::class, 'removeItems']);
            Route::delete('/batch-handler/{batch_id}', [BatchHandlerController::class, 'deleteBatch']);
        });



    Route::prefix('communications')->group(function () {
        Route::get('/', [CommunicationController::class, 'index'])->name('communications.index');
        Route::resource('transactions', CommunicationController::class)->names('communications.transactions');

        Route::prefix('actions')->name('communications.actions.')->group(function () {
            Route::post('add-to-cart', [CommunicationController::class, 'addToCart'])->name('add-to-cart');
            Route::post('reject', [CommunicationController::class, 'reject'])->name('reject');
            Route::get('validate', [CommunicationController::class, 'validateCommunication'])->name('validate');
            Route::get('transmission', [CommunicationController::class, 'transmission'])->name('transmission');
            Route::get('return-effective', [CommunicationController::class, 'returnEffective'])->name('return-effective');
            Route::get('return-cancel', [CommunicationController::class, 'returnCancel'])->name('return-cancel');
        });

        Route::prefix('export')->name('communications.export.')->group(function () {
            Route::get('print', [CommunicationController::class, 'print'])->name('print');
            Route::get('excel', [CommunicationController::class, 'export'])->name('excel');
        });

        Route::prefix('search')->name('communications.search.')->group(function () {
            Route::get('/', [SearchCommunicationController::class, 'index'])->name('index');
            Route::get('form', [SearchCommunicationController::class, 'form'])->name('form');
            Route::post('advanced', [SearchCommunicationController::class, 'advanced'])->name('advanced');
            Route::get('date-selection', [SearchCommunicationController::class, 'date'])->name('date-selection');
        });

        // Routes pour les fantômes PDF
        Route::prefix('phantom')->name('communications.phantom.')->group(function () {
            Route::get('/{communication}/generate', [PhantomController::class, 'generatePhantom'])->name('generate');
            Route::get('/{communication}/preview', [PhantomController::class, 'previewPhantom'])->name('preview');
        });

        // =================== ROUTES RECORDS CORRIGÉES ===================
        // Routes pour les records de communication avec communication_id dans l'URL
        Route::prefix('{communication}/records')->name('communications.records.')->group(function () {
            Route::get('/', [CommunicationRecordController::class, 'index'])->name('index');
            Route::get('/create', [CommunicationRecordController::class, 'create'])->name('create');
            Route::post('/', [CommunicationRecordController::class, 'store'])->name('store');
            Route::get('/{record}', [CommunicationRecordController::class, 'show'])->name('show');
            Route::get('/{record}/edit', [CommunicationRecordController::class, 'edit'])->name('edit');
            Route::put('/{record}', [CommunicationRecordController::class, 'update'])->name('update');
            Route::delete('/{record}', [CommunicationRecordController::class, 'destroy'])->name('destroy');
        });

        // Routes pour les actions des records (sans communication_id dans l'URL)
        Route::prefix('records')->name('communications.records.')->group(function () {
            Route::get('search', [CommunicationRecordController::class, 'searchRecords'])->name('search');
            Route::post('return-effective', [CommunicationRecordController::class, 'returnEffective'])->name('return-effective');
            Route::post('return-cancel', [CommunicationRecordController::class, 'returnCancel'])->name('return-cancel');
        });
        // =================== FIN ROUTES RECORDS ===================

        Route::prefix('reservations')->name('communications.reservations.')->group(function () {
            Route::get('/', [ReservationController::class, 'index'])->name('index');
            Route::get('/approved', [ReservationController::class, 'listApproved'])->name('approved.list');
            Route::get('/approved-reservations', [ReservationController::class, 'listApprovedReservations'])->name('approved.reservations');
            Route::get('/return-available', [ReservationController::class, 'returnAvailable'])->name('return.available');
            Route::post('/{reservation}/mark-returned', [ReservationController::class, 'markAsReturned'])->name('mark.returned');
            Route::get('/pending', [ReservationController::class, 'pending'])->name('pending');
            Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
            Route::get('/{reservation}/edit', [ReservationController::class, 'edit'])->name('edit');
            Route::put('/{reservation}', [ReservationController::class, 'update'])->name('update');
            Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('destroy');

            Route::prefix('actions')->name('actions.')->group(function () {
                Route::post('approved', [ReservationController::class, 'approved'])->name('approved');
            });

            Route::prefix('search')->name('search.')->group(function () {
                Route::get('/', [SearchReservationController::class, 'index'])->name('index');
                Route::get('date-selection', [SearchReservationController::class, 'date'])->name('date-selection');
            });

            Route::prefix('{reservation}/records')->name('records.')->group(function () {
                Route::get('/', [ReservationRecordController::class, 'index'])->name('index');
                Route::get('/create', [ReservationRecordController::class, 'create'])->name('create');
                Route::post('/', [ReservationRecordController::class, 'store'])->name('store');
                Route::get('/{reservationRecord}', [ReservationRecordController::class, 'show'])->name('show');
                Route::get('/{reservationRecord}/edit', [ReservationRecordController::class, 'edit'])->name('edit');
                Route::put('/{reservationRecord}', [ReservationRecordController::class, 'update'])->name('update');
                Route::delete('/{reservationRecord}', [ReservationRecordController::class, 'destroy'])->name('destroy');
            });
        });
    });





    Route::prefix('repositories')->group(function () {

         // Physical Records Management (harmonized route)
        Route::get('physical', [RecordController::class, 'indexPhysical'])->name('records.physical');

         // Digital Folders Management (Phase 10 - Task 10.2)
        Route::resource('folders', \App\Http\Controllers\Web\FolderController::class);
        Route::post('folders/{folder}/move', [\App\Http\Controllers\Web\FolderController::class, 'move'])->name('folders.move');
        Route::get('folders/tree/view', [\App\Http\Controllers\Web\FolderController::class, 'treeView'])->name('folders.tree.view');
        Route::get('folders/tree/data', [\App\Http\Controllers\Web\FolderController::class, 'tree'])->name('folders.tree');

        // Digital Documents Management (Phase 10 - Task 10.3)
        Route::resource('documents', \App\Http\Controllers\Web\DocumentController::class);
        Route::post('documents/{document}/upload', [\App\Http\Controllers\Web\DocumentController::class, 'upload'])->name('documents.upload');
        Route::post('documents/{document}/approve', [\App\Http\Controllers\Web\DocumentController::class, 'approve'])->name('documents.approve');
        Route::post('documents/{document}/reject', [\App\Http\Controllers\Web\DocumentController::class, 'reject'])->name('documents.reject');
        Route::get('documents/{document}/versions', [\App\Http\Controllers\Web\DocumentController::class, 'versions'])->name('documents.versions');
        Route::get('documents/{document}/versions/{version}/download', [\App\Http\Controllers\Web\DocumentController::class, 'downloadVersion'])->name('documents.versions.download');

        // Document Workflow - Check-out/Check-in (Phase 3 - Critical)
        Route::post('documents/{document}/checkout', [\App\Http\Controllers\Web\DocumentController::class, 'checkout'])->name('documents.checkout');
        Route::post('documents/{document}/checkin', [\App\Http\Controllers\Web\DocumentController::class, 'checkin'])->name('documents.checkin');
        Route::post('documents/{document}/cancel-checkout', [\App\Http\Controllers\Web\DocumentController::class, 'cancelCheckout'])->name('documents.cancel-checkout');

        // Document Workflow - Signature (Phase 3 - Critical)
        Route::post('documents/{document}/sign', [\App\Http\Controllers\Web\DocumentController::class, 'sign'])->name('documents.sign');
        Route::post('documents/{document}/verify-signature', [\App\Http\Controllers\Web\DocumentController::class, 'verifySignature'])->name('documents.verify-signature');
        Route::post('documents/{document}/revoke-signature', [\App\Http\Controllers\Web\DocumentController::class, 'revokeSignature'])->name('documents.revoke-signature');

        // Document Workflow - Version Management (Phase 3 - Critical)
        Route::post('documents/{document}/versions/{version}/restore', [\App\Http\Controllers\Web\DocumentController::class, 'restoreVersion'])->name('documents.versions.restore');
        Route::get('documents/{document}/download', [\App\Http\Controllers\Web\DocumentController::class, 'download'])->name('documents.download');




        Route::post('/slips/store', [SlipController::class, 'storetransfert'])->name('slips.storetransfert');
        Route::get('/', [RecordController::class, 'index']);
        Route::get('shelve', [SearchRecordController::class, 'selectShelve'])->name('record-select-shelve');
        Route::post('dolly/create-with-records', [DollyController::class, 'createWithRecords'])->name('dolly.createWithRecords');
        // Routes spécifiques AVANT la route resource (pour éviter les conflits)
        // Dedicated export/import controllers for records
        Route::get('records/exportButton', [\App\Http\Controllers\RecordExportController::class, 'exportButton'])->name('records.exportButton');
        Route::post('records/print', [\App\Http\Controllers\RecordExportController::class, 'printRecords'])->name('records.print');
        Route::post('records/export', [\App\Http\Controllers\RecordExportController::class, 'export'])->name('records.export');
        Route::get('records/export', [\App\Http\Controllers\RecordExportController::class, 'exportForm'])->name('records.export.form');
        Route::get('records/import', [\App\Http\Controllers\RecordImportController::class, 'importForm'])->name('records.import.form');
        Route::post('records/import', [\App\Http\Controllers\RecordImportController::class, 'import'])->name('records.import');
        Route::post('records/analyze-file', [\App\Http\Controllers\RecordImportController::class, 'analyzeFile'])->name('records.analyze-file');
        Route::get('records/terms/autocomplete', [RecordController::class, 'autocompleteTerms'])->name('records.terms.autocomplete');
        Route::get('records/{record}/attachments', [RecordController::class, 'getAttachments'])->name('records.attachments.list');
        Route::get('records/create/full', [RecordController::class, 'createFull'])->name('records.create.full');
        Route::get('records/{record}/full', [RecordController::class, 'showFull'])->name('records.showFull');
        Route::get('search', [RecordController::class, 'search'])->name('records.search');

        // Export SEDA 2.1
        Route::get('records/{record}/export/seda', [SEDAExportController::class, 'exportRecord'])->name('records.export.seda');

        // Routes containers AVANT la route resource
        Route::post('records/container/insert', [RecordContainerController::class, 'store'])->name('record-container-insert');
        Route::post('records/container/remove', [RecordContainerController::class, 'destroy'])->name('record-container-remove');

        // Routes Drag & Drop AVANT la route resource (déplacées vers RecordDragDropController)
        Route::get('records/drag-drop', [RecordDragDropController::class, 'dragDropForm'])->name('records.drag-drop');
        Route::post('records/drag-drop', [RecordDragDropController::class, 'processDragDrop'])->name('records.drag-drop.process');

        // Route resource principale (génère automatiquement show, create, store, edit, update, destroy)
        Route::resource('records', RecordController::class);

        // Routes pour les mots-clés
        Route::prefix('keywords')->name('keywords.')->group(function () {
            Route::get('/', [KeywordController::class, 'index'])->name('index');
            Route::get('/manage', function() { return view('keywords.index'); })->name('manage');
            Route::get('/search', [KeywordController::class, 'search'])->name('search');
            Route::post('/', [KeywordController::class, 'store'])->name('store');
            Route::post('/process', [KeywordController::class, 'processKeywords'])->name('process');
            Route::put('/{keyword}', [KeywordController::class, 'update'])->name('update');
            Route::delete('/{keyword}', [KeywordController::class, 'destroy'])->name('destroy');
        });

        // Routes imbriquées
        Route::resource('records.attachments', RecordAttachmentController::class);
        Route::post('attachments/upload-temp', [RecordAttachmentController::class, 'uploadTemp'])->name('attachments.upload-temp');
        Route::get('upload-diagnostics', [RecordAttachmentController::class, 'diagnostics'])->name('upload.diagnostics');

        Route::resource('authors', RecordAuthorController::class)->names('record-author');
        Route::get('authors/list', [RecordAuthorController::class, 'list'])->name('record-author.list');

        // Author contacts routes
        Route::resource('author-contact', AuthorContactController::class);
        Route::get('authors/{author}/contacts/create', [AuthorContactController::class, 'create'])->name('author-contact.create.for-author');

        Route::resource('records.child', RecordChildController::class)->names('record-child');

        // Gestion du cycle de vie des documents

        Route::get('recordtotransfer', [LifeCycleController::class, 'recordToTransfer'])->name('records.totransfer');
        Route::get('recordtosort', [LifeCycleController::class, 'recordToSort'])->name('records.tosort');
        Route::get('recordtoeliminate', [LifeCycleController::class, 'recordToEliminate'])->name('records.toeliminate');
        Route::get('recordtokeep', [LifeCycleController::class, 'recordToKeep'])->name('records.tokeep');
        Route::get('recordtoretain', [LifeCycleController::class, 'recordToRetain'])->name('records.toretain');
        Route::get('recordtostore', [LifeCycleController::class, 'recordToStore'])->name('records.tostore');

        Route::post('advanced', [SearchRecordController::class, 'advanced'])->name('records.advanced');
        Route::get('advanced/form', [SearchRecordController::class, 'form'])->name('records.advanced.form');
        Route::get('sort', [SearchRecordController::class, 'sort'])->name('records.sort');
        Route::get('select', [SearchRecordController::class, 'date'])->name('record-select-date');
        Route::get('word', [SearchRecordController::class, 'selectWord'])->name('record-select-word');
        Route::get('activity', [SearchRecordController::class, 'selectActivity'])->name('record-select-activity');
        Route::get('building', [SearchRecordController::class, 'selectBuilding'])->name('record-select-building');
        Route::get('last', [SearchRecordController::class, 'selectLast'])->name('record-select-last');
        Route::get('floor', [SearchRecordController::class, 'selectFloor'])->name('record-select-floor');
        Route::get('container', [SearchRecordController::class, 'selectContainer'])->name('record-select-container');
        Route::get('room', [SearchRecordController::class, 'selectRoom'])->name('record-select-room');
    });






    Route::prefix('author-handler')->group(function () {
            Route::resource('/', RecordAuthorController::class)->names('author-handler.index');
            Route::get('list', [RecordAuthorController::class, 'list'])->name('author-handler.list');
            Route::get('list/types', [RecordAuthorController::class, 'getAuthorTypes'])->name('author-handler.types');
            Route::post('store', [RecordAuthorController::class, 'storeAjax'])->name('author-handler.store');
            Route::get('select-modal', [RecordAuthorController::class, 'selectModal'])->name('author-handler.select-modal');
        });

    Route::prefix('activity-handler')->group(function () {
            Route::get('list', [ActivityController::class, 'list'])->name('activity-handler.list');
            Route::get('hierarchy/{id?}', [ActivityController::class, 'hierarchy'])->name('activity-handler.hierarchy');
        });



    Route::prefix('transferrings')->group(function () {
        Route::get('/advanced', [SearchSlipController::class, 'form'])->name('slips.advanced.form');
        Route::post('/advanced', [SearchSlipController::class, 'advanced'])->name('search.slips.advanced');
        Route::get('/', [SlipController::class, 'index']);
        Route::get('slips/export', [SlipController::class, 'exportForm'])->name('slips.export.form');
        Route::post('slips/export', [SlipController::class, 'export'])->name('slips.export');
        Route::get('slips/import', [SlipController::class, 'importForm'])->name('slips.import.form');
        Route::post('slips/import/{format}', [SlipController::class, 'import'])->name('slips.import');
        Route::get('search', [SearchController::class, 'index'])->name('transferrings.search');
        Route::get('slips/reception', [SlipController::class, 'reception'])->name('slips.reception');
        Route::get('slips/approve', [SlipController::class, 'approve'])->name('slips.approve');
        Route::get('slips/integrate', [SlipController::class, 'integrate'])->name('slips.integrate');
        Route::resource('slips', SlipController::class);

        // Export SEDA 2.1
        Route::get('slips/{slip}/export/seda', [SEDAExportController::class, 'exportSlip'])->name('slips.export.seda');
            Route::resource('slips.records', SlipRecordController::class);
            Route::resource('slips.records.containers', SlipRecordContainerController::class);
            Route::resource('containers', SlipContainerController::class)->names('slips.containers');
            Route::get('slip/sort', [SearchSlipController::class, 'index'])->name('slips-sort');
            Route::get('/slips/{slip}/print', [SlipController::class, 'print'])->name('slips.print');
            Route::get('slip/select', [SearchSlipController::class, 'date'])->name('slips-select-date');
            Route::get('organisation/select', [SearchSlipController::class, 'organisation'])->name('slips-select-organisation');
            Route::post('slipRecordAttachment/upload', [SlipRecordAttachmentController::class, 'upload'])->name('slip-record-upload');
            Route::post('slipRecordAttachment/show', [SlipRecordAttachmentController::class, 'show'])->name('slip-record-show');
            Route::delete('slips/{slip}/records/{record}/attachments/{id}', [SlipRecordAttachmentController::class, 'delete'])
                ->name('slipRecordAttachment.delete');

        });

    Route::prefix('deposits')->group(function () {
        Route::get('/', [BuildingController::class, 'index']);
        Route::resource('buildings', BuildingController::class);
        Route::resource('buildings.floors', FloorController::class)->names('floors');
        Route::resource('rooms', RoomController::class);
        Route::resource('shelves', ShelfController::class);
        Route::resource('containers', ContainerController::class);
        Route::resource('trolleys', BuildingController::class);
    });


    Route::prefix('settings')->group(function () {
        Route::get('', [SettingController::class, 'home'])->name('settings.home');

        Route::resource('definitions', SettingController::class)->names('settings.definitions');
        Route::resource('categories', SettingCategoryController::class)->names('settings.categories');
        Route::resource('prompts', PromptManagementController::class)->names('settings.prompts');

        // Routes supplémentaires pour les catégories
        Route::get('categories/tree', [SettingCategoryController::class, 'tree'])->name('settings.categories.tree');
        Route::get('categories/{id}/settings', [SettingCategoryController::class, 'getSettings'])->name('settings.categories.settings');

        // Routes supplémentaires pour les paramètres
        Route::post('definitions/{id}/set-value', [SettingController::class, 'setValue'])->name('settings.definitions.set-value');
        Route::delete('definitions/{id}/reset-value', [SettingController::class, 'resetValue'])->name('settings.definitions.reset-value');

        Route::get('activities/export/excel', [ActivityController::class, 'exportExcel'])->name('activities.export.excel');
        Route::get('activities/export/pdf', [ActivityController::class, 'exportPdf'])->name('activities.export.pdf');
        Route::get('users', [UserController::class, 'index'] );
        Route::resource('user-organisation-role', UserOrganisationRoleController::class);
        Route::resource('user-roles', UserRoleController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::resource('role_permissions', RolePermissionController::class);
        Route::get('role_permissions/{role}/permissions', [RolePermissionController::class, 'getRolePermissions'])->name('role_permissions.get_permissions');
        Route::put('role_permissions/matrix', [RolePermissionController::class, 'updateMatrix'])->name('role_permissions.update_matrix');
        Route::resource('mail-typology', MailTypologyController::class);
        Route::resource('mail-priority', MailPriorityController::class);
        Route::resource('container-status', ContainerStatusController::class);
        Route::resource('container-property', ContainerPropertyController::class);
        Route::resource('sorts', SortController::class);
        Route::resource('languages', LanguageController::class);
        Route::resource('record-supports', RecordSupportController::class);
        Route::resource('record-statuses', RecordStatusController::class);
        Route::resource('transferring-status', SlipStatusController::class);
        Route::resource('mail-action', MailActionController::class);
        Route::resource('logs', LogController::class)->only(['index', 'show']);
        Route::resource('backups', BackupController::class);
        Route::resource('backups.files', BackupFileController::class);
        Route::resource('backups.plannings', BackupPlanningController::class);

        // System Updates Routes
        Route::middleware(['auth'])->prefix('system/updates')->group(function () {
            Route::get('/', [App\Http\Controllers\SystemUpdateController::class, 'index'])->name('system.updates.index');
            Route::post('/check', [App\Http\Controllers\SystemUpdateController::class, 'checkVersions'])->name('system.updates.check');
            Route::post('/update/{version}', [App\Http\Controllers\SystemUpdateController::class, 'updateToVersion'])->name('system.updates.update');
            Route::get('/history', [App\Http\Controllers\SystemUpdateController::class, 'history'])->name('system.updates.history');
            Route::post('/rollback', [App\Http\Controllers\SystemUpdateController::class, 'rollback'])->name('system.updates.rollback');
        });

        // API routes for system updates
        Route::middleware(['auth'])->prefix('api/system/updates')->name('api.system.updates.')->group(function () {
            Route::get('/versions', [App\Http\Controllers\SystemUpdateController::class, 'getVersions'])->name('versions');
            Route::get('/changelog/{version}', [App\Http\Controllers\SystemUpdateController::class, 'getChangelog'])->name('changelog');
            Route::get('/progress', [App\Http\Controllers\SystemUpdateController::class, 'getUpdateProgress'])->name('progress');
        });

    });



    Route::prefix('dollies')->group(function () {
        Route::get('/', [DollyController::class, 'index']);
        Route::get('list', [DollyController::class, 'apiList'])->name('dollies.list');
        Route::post('store', [DollyController::class, 'apiCreate'])->name('dollies.store');
        Route::post('create-with-communications', [DollyController::class, 'createWithCommunications'])->name('dolly.createWithCommunications');
        Route::resource('dolly', DollyController::class)->names('dolly');
        Route::get('action', [DollyActionController::class, 'index'])->name('dollies.action');
        Route::get('sort', [SearchdollyController::class, 'index'])->name('dollies-sort');
        Route::delete('{dolly}/remove-record/{record}', [DollyController::class, 'removeRecord'])->name('dolly.remove-record');
        Route::delete('{dolly}/remove-mail/{mail}', [DollyController::class, 'removeMail'])->name('dolly.remove-mail');
        Route::post('{dolly}/add-record', [DollyController::class, 'addRecord'])->name('dolly.add-record');
        Route::post('{dolly}/add-mail', [DollyController::class, 'addMail'])->name('dolly.add-mail');
        Route::post('{dolly}/add-communication', [DollyController::class, 'addCommunication'])->name('dolly.add-communication');
        Route::delete('{dolly}/remove-communication/{communication}', [DollyController::class, 'removeCommunication'])->name('dolly.remove-communication');
        Route::post('{dolly}/add-room', [DollyController::class, 'addRoom'])->name('dolly.add-room');
        Route::delete('{dolly}/remove-room/{room}', [DollyController::class, 'removeRoom'])->name('dolly.remove-room');
        Route::post('{dolly}/add-container', [DollyController::class, 'addContainer'])->name('dolly.add-container');
        Route::delete('{dolly}/remove-container/{container}', [DollyController::class, 'removeContainer'])->name('dolly.remove-container');
        Route::post('{dolly}/add-shelve', [DollyController::class, 'addShelve'])->name('dolly.add-shelve');
        Route::delete('{dolly}/remove-shelve/{shelve}', [DollyController::class, 'removeShelve'])->name('dolly.remove-shelve');
        Route::post('{dolly}/add-slip-record', [DollyController::class, 'addSlipRecord'])->name('dolly.add-slip-record');
        Route::delete('{dolly}/remove-slip-record/{slipRecord}', [DollyController::class, 'removeSlipRecord'])->name('dolly.remove-slip-record');

        // Routes pour dossiers numériques
        Route::post('{dolly}/add-digital-folder', [DollyController::class, 'addDigitalFolder'])->name('dolly.add-digital-folder');
        Route::delete('{dolly}/remove-digital-folder/{folder}', [DollyController::class, 'removeDigitalFolder'])->name('dolly.remove-digital-folder');

        // Routes pour documents numériques
        Route::post('{dolly}/add-digital-document', [DollyController::class, 'addDigitalDocument'])->name('dolly.add-digital-document');
        Route::delete('{dolly}/remove-digital-document/{document}', [DollyController::class, 'removeDigitalDocument'])->name('dolly.remove-digital-document');
    });



    Route::prefix('tools')->group(function () {
        Route::get('/', [ActivityController::class , 'index' ] );
        Route::resource('activities', ActivityController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::resource('activities.retentions', retentionActivityController::class);
        Route::resource('retentions', RetentionController::class);
        Route::resource('retentions.exigences', RetentionLawArticleController::class);
        Route::resource('laws', LawController::class);
        Route::resource('laws.Articles', LawArticleController::class);
        Route::resource('communicabilities', CommunicabilityController::class);

        Route::resource('activities.communicabilities', activityCommunicabilityController::class);
        Route::resource('organisations.rooms', OrganisationRoomController::class);
        Route::resource('organisations.activities', OrganisationActivityController::class);
        Route::resource('organisations.contacts', \App\Http\Controllers\OrganisationContactController::class)
            ->names('organisations.contacts');

        // Routes pour la gestion des organisations dans tools
        Route::get('organisations/export/excel', [OrganisationController::class, 'exportExcel'])->name('organisations.export.excel');
        Route::get('organisations/export/pdf', [OrganisationController::class, 'exportPdf'])->name('organisations.export.pdf');
        Route::resource('organisations', OrganisationController::class);

        Route::resource('access', ContainerStatusController::class);
        Route::get('barcode', [BarcodeController::class,'create'])->name('barcode.create');
        Route::post('/barcodes/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
        Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::post('/barcodes/generate', [BarcodeController::class, 'generate'])->name('barcode.generate');

        // Groupe de routes pour la gestion du thésaurus (intégré dans tools)
        Route::prefix('thesaurus')->group(function () {
            // Page d'accueil du module
            Route::resource('/',ThesaurusController::class)->names('thesaurus');

            // Routes pour la gestion des schémas de thésaurus
            Route::resource('schemes', ThesaurusSchemeController::class)->names('thesaurus.schemes');

            // Relations associatives - contrôleur existe
            Route::get('associative-relations', [ThesaurusAssociativeRelationController::class, 'index'])->name('thesaurus.associative_relations.index');
            Route::get('associative-relations/create/{term}', [ThesaurusAssociativeRelationController::class, 'create'])->name('thesaurus.associative_relations.create');
            Route::post('associative-relations/store/{term}', [ThesaurusAssociativeRelationController::class, 'store'])->name('thesaurus.associative_relations.store');
            Route::delete('associative-relations/destroy/{termId}/{associatedTermId}', [ThesaurusAssociativeRelationController::class, 'destroy'])->name('thesaurus.associative_relations.destroy');

            // Routes pour les traductions - contrôleur existe
            Route::get('translations', [ThesaurusTranslationController::class, 'index'])->name('thesaurus.translations.index');
            Route::get('translations/create/{term}', [ThesaurusTranslationController::class, 'create'])->name('thesaurus.translations.create');
            Route::post('translations/store/{term}', [ThesaurusTranslationController::class, 'store'])->name('thesaurus.translations.store');
            Route::delete('translations/destroy/{sourceTermId}/{targetTermId}', [ThesaurusTranslationController::class, 'destroy'])->name('thesaurus.translations.destroy');

            // Routes pour la recherche avancée
            Route::get('search', [ThesaurusSearchController::class, 'index'])->name('thesaurus.search.index');
            Route::get('search/results', [ThesaurusSearchController::class, 'search'])->name('thesaurus.search.results');

            // Routes pour les fonctionnalités thésaurus (via ThesaurusController)
            Route::get('concepts', [ThesaurusController::class, 'concepts'])->name('thesaurus.concepts');
            Route::get('concepts/{concept}', [ThesaurusController::class, 'showConcept'])->name('thesaurus.concepts.show');
            Route::get('hierarchy', [ThesaurusController::class, 'hierarchy'])->name('thesaurus.hierarchy');
            Route::get('search/autocomplete', [ThesaurusController::class, 'autocomplete'])->name('thesaurus.autocomplete');

            // Routes manquantes pour les fonctionnalités du thésaurus
            Route::get('export-import', [ThesaurusController::class, 'importExport'])->name('thesaurus.export-import');
            Route::get('record-concept-relations', [ThesaurusController::class, 'recordConceptRelations'])->name('thesaurus.record-concept-relations');
            Route::get('statistics', [ThesaurusController::class, 'statistics'])->name('thesaurus.statistics');
            Route::post('export-scheme', [ThesaurusController::class, 'exportScheme'])->name('thesaurus.export-scheme');
            Route::post('import-file', [ThesaurusController::class, 'importFile'])->name('thesaurus.import-file');
            Route::post('auto-associate-concepts', [ThesaurusController::class, 'autoAssociateConcepts'])->name('thesaurus.auto-associate-concepts');

            // Routes pour les relations hiérarchiques
            Route::get('hierarchical-relations/{term}', [ThesaurusController::class, 'hierarchicalRelationsIndex'])->name('thesaurus.hierarchical_relations.index');
            Route::get('hierarchical-relations/{term}/broader/create', [ThesaurusController::class, 'createBroaderRelation'])->name('thesaurus.hierarchical_relations.broader.create');
            Route::post('hierarchical-relations/{term}/broader/store', [ThesaurusController::class, 'storeBroaderRelation'])->name('thesaurus.hierarchical_relations.broader.store');
            Route::get('hierarchical-relations/{term}/narrower/create', [ThesaurusController::class, 'createNarrowerRelation'])->name('thesaurus.hierarchical_relations.narrower.create');
            Route::post('hierarchical-relations/{term}/narrower/store', [ThesaurusController::class, 'storeNarrowerRelation'])->name('thesaurus.hierarchical_relations.narrower.store');
            Route::delete('hierarchical-relations/{term}/{relationType}/{relatedTerm}', [ThesaurusController::class, 'destroyHierarchicalRelation'])->name('thesaurus.hierarchical_relations.destroy');
        });

    });


    // Routes pour les rapports
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [ReportController::class, 'dashboard'])->name('report.dashboard');
        Route::get('mails', [ReportController::class, 'statisticsMails'])->name('report.statistics.mails');
        Route::get('repositories', [ReportController::class, 'statisticsRepositories'])->name('report.statistics.repositories');
        Route::get('communications', [ReportController::class, 'statisticsCommunications'])->name('report.statistics.communications');
        Route::get('transferrings', [ReportController::class, 'statisticsTransferrings'])->name('report.statistics.transferrings');
        Route::get('deposits', [ReportController::class, 'statisticsDeposits'])->name('report.statistics.deposits');
        Route::get('tools', [ReportController::class, 'statisticsTools'])->name('report.statistics.tools');
        Route::get('dollies', [ReportController::class, 'statisticsDollies'])->name('report.statistics.dollies');
    });

    Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');    // routes/web.php - Routes Ollama



    // Le module Workflow a été supprimé

// Routes d'administration du Rate Limiting et OPAC
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Rate Limiting Administration
    Route::get('rate-limit/dashboard', [RateLimitController::class, 'dashboard'])->name('rate-limit.dashboard');
    Route::get('rate-limit/user-stats', [RateLimitController::class, 'userStats'])->name('rate-limit.user-stats');
    Route::post('rate-limit/clear', [RateLimitController::class, 'clearLimits'])->name('rate-limit.clear');

    // OPAC Configuration Administration
    // TODO: Implement OpacConfigurationController and related Admin controllers
    /*
    Route::prefix('opac')->name('opac.')->group(function () {
        Route::get('configurations', [OpacConfigurationController::class, 'index'])->name('configurations.index');
        Route::post('configurations', [OpacConfigurationController::class, 'update'])->name('configurations.update');
        Route::get('configurations/{configuration}', [OpacConfigurationController::class, 'show'])->name('configurations.show');
        Route::post('configurations/{configuration}/reset', [OpacConfigurationController::class, 'reset'])->name('configurations.reset');
        Route::post('configurations/export', [OpacConfigurationController::class, 'export'])->name('configurations.export');
        Route::post('configurations/import', [OpacConfigurationController::class, 'import'])->name('configurations.import');

        // OPAC Users Administration
        Route::resource('users', AdminPublicUserController::class);
        Route::post('users/{user}/approve', [AdminPublicUserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/disapprove', [AdminPublicUserController::class, 'disapprove'])->name('users.disapprove');

        // OPAC Pages Administration
        Route::resource('pages', \App\Http\Controllers\Admin\OpacPageController::class);
        Route::post('pages/bulk-publish', [\App\Http\Controllers\Admin\OpacPageController::class, 'bulkPublish'])->name('pages.bulk-publish');

        // OPAC Templates Administration - Moved to Public Module
    });
    */
});

// Routes publics de administration du module public
Route::prefix('public')->name('public.')->group(function () {
    Route::resource('users', PublicUserController::class)->names('users');
    Route::patch('users/{user}/activate', [PublicUserController::class, 'activate'])->name('users.activate');
    Route::patch('users/{user}/deactivate', [PublicUserController::class, 'deactivate'])->name('users.deactivate');

    // Gestion des discussions et messages
    Route::resource('chats', PublicChatController::class)->names('chats');
    Route::resource('chat-participants', PublicChatParticipantController::class)->names('chat-participants');
    Route::resource('chats.messages', PublicChatMessageController::class)->names('chats.messages');

    // Gestion des événements publics
    Route::resource('events', PublicEventController::class)->names('events');
    Route::post('events/bulk-action', [PublicEventController::class, 'bulkAction'])->name('events.bulk-action');
    Route::get('events/{event}/registrations', [PublicEventController::class, 'registrations'])->name('events.registrations');
    Route::get('events/{event}/export-registrations', [PublicEventController::class, 'exportRegistrations'])->name('events.export-registrations');
    Route::post('events/{event}/registrations/{registration}/status', [PublicEventController::class, 'updateRegistrationStatus'])->name('events.registrations.status');
    Route::resource('event-registrations', PublicEventRegistrationController::class)->names('event-registrations');

    // Gestion du contenu public
    Route::resource('news', PublicNewsController::class)->names('news');
    Route::resource('pages', PublicPageController::class)->names('pages');
    Route::post('pages/bulk-action', [PublicPageController::class, 'bulkAction'])->name('pages.bulk-action');
    Route::post('pages/reorder', [PublicPageController::class, 'reorder'])->name('pages.reorder');
    Route::resource('templates', PublicTemplateController::class)->names('templates');

    // Route de test des éditeurs WYSIWYG
    Route::get('test-editors', function () {
        return view('public.test-editors');
    })->name('test-editors');

    // OPAC Configuration Management
    Route::resource('configurations', \App\Http\Controllers\OPAC\ConfigurationController::class)->only(['index', 'show', 'update'])->names('configurations');
    Route::post('configurations/{configuration}/reset', [\App\Http\Controllers\OPAC\ConfigurationController::class, 'reset'])->name('configurations.reset');
    Route::post('configurations/export', [\App\Http\Controllers\OPAC\ConfigurationController::class, 'export'])->name('configurations.export');
    Route::post('configurations/import', [\App\Http\Controllers\OPAC\ConfigurationController::class, 'import'])->name('configurations.import');

    // OPAC Templates Management - Portail Administration
    Route::resource('opac-templates', \App\Http\Controllers\OPAC\TemplateController::class)->names('opac-templates');
    Route::get('opac-templates/{template}/preview', [\App\Http\Controllers\OPAC\TemplateController::class, 'preview'])->name('opac-templates.preview');
    Route::post('opac-templates/{template}/duplicate', [\App\Http\Controllers\OPAC\TemplateController::class, 'duplicate'])->name('opac-templates.duplicate');
    Route::get('opac-templates/{template}/export', [\App\Http\Controllers\OPAC\TemplateController::class, 'export'])->name('opac-templates.export');

    // Gestion des demandes de documents
    Route::resource('document-requests', PublicDocumentRequestController::class)->names('document-requests');
    Route::get('records/autocomplete', [PublicRecordController::class, 'autocomplete'])->name('records.autocomplete');
    Route::resource('records', PublicRecordController::class)->names('records');
    Route::resource('responses', PublicResponseController::class)->names('responses');
    Route::resource('response-attachments', PublicResponseAttachmentController::class)->names('response-attachments');

    // Gestion des retours et recherches
    Route::resource('feedback', PublicFeedbackController::class)->names('feedback');
    Route::put('feedback/{feedback}/status', [PublicFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
    Route::post('feedback/{feedback}/comments', [PublicFeedbackController::class, 'addComment'])->name('feedback.add-comment');
    Route::delete('feedback/{feedback}/comments/{comment}', [PublicFeedbackController::class, 'deleteComment'])->name('feedback.delete-comment');
    Route::resource('search-logs', PublicSearchLogController::class)->only(['index', 'show'])->names('search-logs');

    // Dashboard et statistiques du module public
    Route::get('dashboard', [PublicUserController::class, 'dashboard'])->name('dashboard');
    Route::get('statistics', [PublicUserController::class, 'statistics'])->name('statistics');
});

});

// Routes pour l'assistant IA de recherche (hors API)
Route::middleware(['auth'])->prefix('ai-search')->name('ai-search.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AiSearchController::class, 'index'])->name('index');
    Route::post('/chat', [\App\Http\Controllers\AiSearchController::class, 'chat'])->name('chat');

    // Documentation de l'IA
    Route::get('/documentation', [\App\Http\Controllers\AiSearchController::class, 'documentation'])->name('documentation');

    // Routes de test
    Route::get('/tests', [\App\Http\Controllers\AiSearchTestController::class, 'runTests'])->name('tests');
    Route::get('/test/{testName}', [\App\Http\Controllers\AiSearchTestController::class, 'runTest'])->name('test.single');
    Route::get('/test-interface', [\App\Http\Controllers\AiSearchTestController::class, 'testInterface'])->name('test.interface');
});

// Routes OPAC (Online Public Access Catalog) - Accès public sans authentification
// New modular OPAC architecture with specialized controllers
Route::prefix('opac')->name('opac.')->middleware('opac.errors')->group(function () {
    // Home page - redirect to search
    Route::get('/', function() {
        return redirect()->route('opac.search');
    })->name('index');

    // Authentication routes for public users
    Route::get('/login', [\App\Http\Controllers\OPAC\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\OPAC\AuthController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\OPAC\AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\OPAC\AuthController::class, 'register']);
    Route::post('/logout', [\App\Http\Controllers\OPAC\AuthController::class, 'logout'])->name('logout');

    // News routes
    Route::get('/news', [\App\Http\Controllers\OPAC\NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{news}', [\App\Http\Controllers\OPAC\NewsController::class, 'show'])->name('news.show');

    // Pages routes
    Route::get('/pages', [\App\Http\Controllers\OPAC\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}', [\App\Http\Controllers\OPAC\PageController::class, 'show'])->name('pages.show');

    // Events routes
    Route::get('/events', [\App\Http\Controllers\OPAC\EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [\App\Http\Controllers\OPAC\EventController::class, 'show'])->name('events.show');

    // Records and Search routes (public access)
    Route::get('/records', [\App\Http\Controllers\OPAC\RecordController::class, 'index'])->name('records.index');
    Route::get('/records/search', [\App\Http\Controllers\OPAC\RecordController::class, 'search'])->name('records.search');
    Route::get('/records/autocomplete', [\App\Http\Controllers\OPAC\RecordController::class, 'autocomplete'])->name('records.autocomplete');
    Route::get('/records/{id}', [\App\Http\Controllers\OPAC\RecordController::class, 'show'])->name('records.show');

    // Digital Collections
    Route::get('/digital/folders', [\App\Http\Controllers\OPAC\DigitalFolderController::class, 'index'])->name('digital.folders.index');
    Route::get('/digital/folders/{id}', [\App\Http\Controllers\OPAC\DigitalFolderController::class, 'show'])->name('digital.folders.show');
    Route::get('/digital/documents/{id}', [\App\Http\Controllers\OPAC\DigitalDocumentController::class, 'show'])->name('digital.documents.show');
    Route::get('/digital/documents/{id}/download', [\App\Http\Controllers\OPAC\DigitalDocumentController::class, 'download'])->name('digital.documents.download');

    // Search routes - Primary search interface
    Route::get('/search', [\App\Http\Controllers\OPAC\SearchController::class, 'index'])->name('search');
    Route::get('/search/advanced', [\App\Http\Controllers\OPAC\SearchController::class, 'index'])->name('search.index');
    Route::post('/search', [\App\Http\Controllers\OPAC\SearchController::class, 'search'])->name('search.results');
    Route::get('/search/suggestions', [\App\Http\Controllers\OPAC\SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/api/search', [\App\Http\Controllers\OPAC\SearchController::class, 'apiSearch'])->name('api.search');

    // Feedback routes (mixed access)
    Route::get('/feedback', [\App\Http\Controllers\OPAC\FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [\App\Http\Controllers\OPAC\FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/success', [\App\Http\Controllers\OPAC\FeedbackController::class, 'success'])->name('feedback.success');

    // Protected routes for authenticated public users
    Route::middleware('auth:public')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\OPAC\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/activity', [\App\Http\Controllers\OPAC\DashboardController::class, 'activity'])->name('dashboard.activity');
        Route::get('/dashboard/quick-actions', [\App\Http\Controllers\OPAC\DashboardController::class, 'quickActions'])->name('dashboard.quick-actions');
        Route::get('/dashboard/preferences', [\App\Http\Controllers\OPAC\DashboardController::class, 'preferences'])->name('dashboard.preferences');
        Route::put('/dashboard/preferences', [\App\Http\Controllers\OPAC\DashboardController::class, 'updatePreferences'])->name('dashboard.preferences.update');

        // Profile management
        Route::get('/profile', [\App\Http\Controllers\OPAC\ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\OPAC\ProfileController::class, 'update'])->name('profile.update');

        // Reservations
        Route::get('/reservations', [\App\Http\Controllers\OPAC\ReservationController::class, 'index'])->name('reservations');
        Route::post('/reservations', [\App\Http\Controllers\OPAC\ReservationController::class, 'store'])->name('reservations.store');

        // Document Requests
        Route::get('/document-requests', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'index'])->name('document-requests.index');
        Route::get('/document-requests/create', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'create'])->name('document-requests.create');
        Route::post('/document-requests', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'store'])->name('document-requests.store');
        Route::get('/document-requests/{documentRequest}', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'show'])->name('document-requests.show');
        Route::get('/document-requests/{documentRequest}/edit', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'edit'])->name('document-requests.edit');
        Route::put('/document-requests/{documentRequest}', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'update'])->name('document-requests.update');
        Route::post('/document-requests/{documentRequest}/cancel', [\App\Http\Controllers\OPAC\DocumentRequestController::class, 'cancel'])->name('document-requests.cancel');

        // User feedback management
        Route::get('/my-feedback', [\App\Http\Controllers\OPAC\FeedbackController::class, 'myFeedback'])->name('feedback.my-feedback');
        Route::get('/my-feedback/{id}', [\App\Http\Controllers\OPAC\FeedbackController::class, 'show'])->name('feedback.show');

        // Search history
        Route::get('/search/history', [\App\Http\Controllers\OPAC\SearchController::class, 'history'])->name('search.history');
        Route::delete('/search/history/{searchId}', [\App\Http\Controllers\OPAC\SearchController::class, 'deleteSearch'])->name('search.history.delete');
        Route::post('/search/history/clear', [\App\Http\Controllers\OPAC\SearchController::class, 'clearHistory'])->name('search.history.clear');
        Route::post('/search/save', [\App\Http\Controllers\OPAC\SearchController::class, 'saveSearch'])->name('search.save');

        // Legacy routes (keeping for compatibility)
        Route::get('/requests', [\App\Http\Controllers\OPAC\RequestController::class, 'index'])->name('requests');
        Route::post('/requests', [\App\Http\Controllers\OPAC\RequestController::class, 'store'])->name('requests.store');
    });

    // Template management (public access for viewing)
    Route::get('/templates', [\App\Http\Controllers\OPAC\TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template}', [\App\Http\Controllers\OPAC\TemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{template}/preview', [\App\Http\Controllers\OPAC\TemplateController::class, 'preview'])->name('templates.preview');

    // Template customization (requires authentication)
    Route::middleware('auth:public')->group(function () {
        Route::get('/templates/{template}/customize', [\App\Http\Controllers\OPAC\TemplateController::class, 'customize'])->name('templates.customize');
        Route::post('/templates/apply', [\App\Http\Controllers\OPAC\TemplateController::class, 'apply'])->name('templates.apply');
        Route::post('/templates/save-customization', [\App\Http\Controllers\OPAC\TemplateController::class, 'saveCustomization'])->name('templates.save-customization');
    });
});

// Routes API pour les records et thésaurus
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('thesaurus/search', [ThesaurusController::class, 'searchApi'])->name('api.thesaurus.search');
});

// API routes pour le thésaurus
Route::middleware(['auth'])->prefix('api/thesaurus')->name('api.thesaurus.')->group(function () {
    Route::post('import/skos/process', [App\Http\Controllers\Api\ThesaurusImportController::class, 'processSkosImport'])->name('import.skos.process');
    Route::post('import/csv/process', [App\Http\Controllers\Api\ThesaurusImportController::class, 'processCsvImport'])->name('import.csv.process');
    Route::post('import/rdf/process', [App\Http\Controllers\Api\ThesaurusImportController::class, 'processRdfImport'])->name('import.rdf.process');
    Route::get('import/status/{importId}', [App\Http\Controllers\Api\ThesaurusImportController::class, 'getImportStatus'])->name('import.status');
});

// MCP/AI web proxy routes retirées

// Workflow Management Routes
Route::prefix('workflows')->name('workflows.')->middleware('auth')->group(function () {
    // Workflow Definitions
    Route::get('definitions', [\App\Http\Controllers\WorkflowDefinitionController::class, 'index'])->name('definitions.index');
    Route::get('definitions/create', [\App\Http\Controllers\WorkflowDefinitionController::class, 'create'])->name('definitions.create');
    Route::post('definitions', [\App\Http\Controllers\WorkflowDefinitionController::class, 'store'])->name('definitions.store');
    Route::get('definitions/{definition}', [\App\Http\Controllers\WorkflowDefinitionController::class, 'show'])->name('definitions.show');
    Route::get('definitions/{definition}/edit', [\App\Http\Controllers\WorkflowDefinitionController::class, 'edit'])->name('definitions.edit');
    Route::put('definitions/{definition}', [\App\Http\Controllers\WorkflowDefinitionController::class, 'update'])->name('definitions.update');
    Route::delete('definitions/{definition}', [\App\Http\Controllers\WorkflowDefinitionController::class, 'destroy'])->name('definitions.destroy');

    // BPMN Configuration Routes
    Route::get('definitions/{definition}/configuration/create', [\App\Http\Controllers\WorkflowDefinitionController::class, 'createConfiguration'])->name('definitions.configuration.create');
    Route::post('definitions/{definition}/configuration', [\App\Http\Controllers\WorkflowDefinitionController::class, 'storeConfiguration'])->name('definitions.configuration.store');
    Route::get('definitions/{definition}/configuration/edit', [\App\Http\Controllers\WorkflowDefinitionController::class, 'editConfiguration'])->name('definitions.configuration.edit');
    Route::put('definitions/{definition}/configuration', [\App\Http\Controllers\WorkflowDefinitionController::class, 'updateConfiguration'])->name('definitions.configuration.update');

    // Workflow Instances
    Route::get('instances', [\App\Http\Controllers\WorkflowInstanceController::class, 'index'])->name('instances.index');
    Route::get('instances/create', [\App\Http\Controllers\WorkflowInstanceController::class, 'create'])->name('instances.create');
    Route::post('instances', [\App\Http\Controllers\WorkflowInstanceController::class, 'store'])->name('instances.store');
    Route::get('instances/{instance}', [\App\Http\Controllers\WorkflowInstanceController::class, 'show'])->name('instances.show');
    Route::delete('instances/{instance}', [\App\Http\Controllers\WorkflowInstanceController::class, 'destroy'])->name('instances.destroy');

    // Workflow Instance Actions
    Route::post('instances/{instance}/start', [\App\Http\Controllers\WorkflowInstanceController::class, 'start'])->name('instances.start');
    Route::post('instances/{instance}/pause', [\App\Http\Controllers\WorkflowInstanceController::class, 'pause'])->name('instances.pause');
    Route::post('instances/{instance}/resume', [\App\Http\Controllers\WorkflowInstanceController::class, 'resume'])->name('instances.resume');
    Route::post('instances/{instance}/cancel', [\App\Http\Controllers\WorkflowInstanceController::class, 'cancel'])->name('instances.cancel');
});

// Task Management Routes
Route::prefix('tasks')->name('tasks.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\TaskController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\TaskController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\TaskController::class, 'store'])->name('store');
    Route::get('/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('show');
    Route::get('/{task}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('edit');
    Route::put('/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');
});

// Workplace Invitations
Route::get('/workplaces/invitations/{token}', [\App\Http\Controllers\WorkplaceInvitationController::class, 'accept'])
    ->name('workplaces.invitations.accept');

// WorkPlace Management Routes
Route::prefix('workplaces')->name('workplaces.')->middleware('auth')->group(function () {
    // Main Workplace Routes
    Route::get('/', [\App\Http\Controllers\WorkplaceController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\WorkplaceController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\WorkplaceController::class, 'store'])->name('store');

    // Template Management Routes
    Route::resource('templates', \App\Http\Controllers\WorkplaceTemplateController::class)->names('templates');

    Route::get('/{workplace}', [\App\Http\Controllers\WorkplaceController::class, 'show'])->name('show');
    Route::get('/{workplace}/edit', [\App\Http\Controllers\WorkplaceController::class, 'edit'])->name('edit');
    Route::put('/{workplace}', [\App\Http\Controllers\WorkplaceController::class, 'update'])->name('update');
    Route::delete('/{workplace}', [\App\Http\Controllers\WorkplaceController::class, 'destroy'])->name('destroy');
    Route::post('/{workplace}/archive', [\App\Http\Controllers\WorkplaceController::class, 'archive'])->name('archive');
    Route::get('/{workplace}/settings', [\App\Http\Controllers\WorkplaceController::class, 'settings'])->name('settings');

    // Activity Management Routes
    Route::get('{workplace}/activities', [\App\Http\Controllers\WorkplaceActivityController::class, 'index'])->name('activities.index');

    // Member Management Routes
    Route::prefix('{workplace}/members')->name('members.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WorkplaceMemberController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\WorkplaceMemberController::class, 'store'])->name('store');
        Route::put('/{member}', [\App\Http\Controllers\WorkplaceMemberController::class, 'update'])->name('update');
        Route::delete('/{member}', [\App\Http\Controllers\WorkplaceMemberController::class, 'destroy'])->name('destroy');
        Route::put('/{member}/permissions', [\App\Http\Controllers\WorkplaceMemberController::class, 'updatePermissions'])->name('permissions');
        Route::put('/{member}/notifications', [\App\Http\Controllers\WorkplaceMemberController::class, 'updateNotifications'])->name('notifications');
    });

    // Bookmark Management Routes
    Route::prefix('{workplace}/bookmarks')->name('bookmarks.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WorkplaceBookmarkController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\WorkplaceBookmarkController::class, 'store'])->name('store');
        Route::delete('/{bookmark}', [\App\Http\Controllers\WorkplaceBookmarkController::class, 'destroy'])->name('destroy');
    });

    // Content Management Routes
    Route::prefix('{workplace}/content')->name('content.')->group(function () {
        Route::get('/folders', [\App\Http\Controllers\WorkplaceContentController::class, 'folders'])->name('folders');
        Route::get('/documents', [\App\Http\Controllers\WorkplaceContentController::class, 'documents'])->name('documents');
        Route::post('/folders', [\App\Http\Controllers\WorkplaceContentController::class, 'shareFolder'])->name('shareFolder');
        Route::post('/documents', [\App\Http\Controllers\WorkplaceContentController::class, 'shareDocument'])->name('shareDocument');
        Route::delete('/folders/{folder}', [\App\Http\Controllers\WorkplaceContentController::class, 'unshareFolder'])->name('unshareFolder');
        Route::delete('/documents/{document}', [\App\Http\Controllers\WorkplaceContentController::class, 'unshareDocument'])->name('unshareDocument');
        Route::post('/folders/{folder}/pin', [\App\Http\Controllers\WorkplaceContentController::class, 'pinFolder'])->name('pinFolder');
        Route::post('/documents/{document}/feature', [\App\Http\Controllers\WorkplaceContentController::class, 'featureDocument'])->name('featureDocument');
        Route::get('/documents/{document}/view', [\App\Http\Controllers\WorkplaceContentController::class, 'viewDocument'])->name('viewDocument');
    });
});







Route::get('/new-feature', [App\Http\Controllers\NewFeatureController::class, 'index']);
