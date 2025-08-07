<?php

// Routes MCP (Model Context Protocol)
require __DIR__.'/mcp.php';

// Routes Administration MCP
require __DIR__.'/admin-mcp.php';

// Routes Test Mistral (pour tester l'intégration Mistral)
require __DIR__.'/mistral-test.php';

use App\Http\Controllers\BulletinBoardAdminController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PhantomController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\NotificationController as NewNotificationController;
use App\Http\Controllers\RateLimitController;
use App\Services\NotificationService;
use App\Enums\NotificationModule;
use App\Enums\NotificationAction;
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
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchReceivedController;
use App\Http\Controllers\BatchSendController;
use App\Http\Controllers\AuthorController;
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
use App\Http\Controllers\ThesaurusSearchController;
use App\Http\Controllers\ThesaurusExportImportController;
use App\Http\Controllers\PublicSearchLogController;
use App\Http\Controllers\RecordController;
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
use App\Http\Controllers\BatchMailController;
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

// Workflow related controllers
use App\Http\Controllers\WorkflowTemplateController;
use App\Http\Controllers\WorkflowStepController;
use App\Http\Controllers\WorkflowInstanceController;
use App\Http\Controllers\WorkflowStepInstanceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\MailTaskController;


// MCP
use App\Http\Controllers\McpProxyController;
use App\Http\Controllers\RecordEnricherController;


Auth::routes();


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
    });

    Route::post('/switch-organisation', [OrganisationController::class, 'switchOrganisation'])->name('switch.organisation');
    Route::get('/', [MailReceivedController::class, 'index'])->name('home');

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

        // Routes pour les notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/organisation', [NewNotificationController::class, 'indexOrganisation'])->name('organisation');
            Route::get('/current', [NewNotificationController::class, 'indexUser'])->name('current');
            Route::get('/{id}', [NewNotificationController::class, 'showView'])->name('show');

            // Routes API pour AJAX
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/organisation', [NewNotificationController::class, 'getForOrganisation'])->name('organisation');
                Route::get('/current', [NewNotificationController::class, 'getForCurrentUser'])->name('current');
                Route::post('/mark-read', [NewNotificationController::class, 'markAsRead'])->name('mark-read');
                Route::post('/mark-all-read', [NewNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
                Route::get('/unread-count', [NewNotificationController::class, 'getUnreadCount'])->name('unread-count');
                Route::post('/cleanup', [NewNotificationController::class, 'cleanup'])->name('cleanup');
                Route::get('/{id}', [NewNotificationController::class, 'show'])->name('show');
            });
        });

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


    Route::prefix('mails')->group(function () {

        Route::resource('container', MailContainerController::class)->names('mail-container');
        Route::get('containers/list', [MailContainerController::class, 'getContainers'])->name('mail-container.list');

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

        Route::resource('received', MailReceivedController::class)->names('mail-received');

        Route::get('received/{mail}/approve', [MailReceivedController::class, 'approve'])->name('mail-received.approve');
        Route::get('received/{mail}/reject', [MailReceivedController::class, 'reject'])->name('mail-received.reject');

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
        Route::resource('batches.mail', BatchMailController::class)->names('batch.mail');
        Route::resource('batch-received', BatchReceivedController::class)->names('batch-received');
        Route::resource('batch-send', BatchSendController::class)->names('batch-send');


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

        // Routes pour les tâches et workflows
        Route::get('tasks', [MailTaskController::class, 'index'])->name('mails.tasks.index');
        Route::get('my-tasks', [MailTaskController::class, 'myTasks'])->name('mails.tasks.my-tasks');
        Route::get('workflows', [MailWorkflowController::class, 'index'])->name('mails.workflows.index');
        Route::get('my-workflows', [MailWorkflowController::class, 'myWorkflows'])->name('mails.workflows.my-workflows');

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
            Route::get('/create', [ReservationController::class, 'create'])->name('create');
            Route::post('/', [ReservationController::class, 'store'])->name('store');
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

            Route::resource('records', ReservationRecordController::class)->names('records');
        });
    });





    Route::prefix('repositories')->group(function () {
        Route::post('/slips/store', [SlipController::class, 'storetransfert'])->name('slips.storetransfert');
        Route::get('/', [RecordController::class, 'index']);
        Route::get('shelve', [SearchRecordController::class, 'selectShelve'])->name('record-select-shelve');
        Route::post('dolly/create-with-records', [DollyController::class, 'createWithRecords'])->name('dolly.createWithRecords');
        // Routes spécifiques AVANT la route resource (pour éviter les conflits)
        Route::get('records/exportButton', [RecordController::class, 'exportButton'])->name('records.exportButton');
        Route::post('records/print', [RecordController::class, 'printRecords'])->name('records.print');
        Route::post('records/export', [RecordController::class, 'export'])->name('records.export');
        Route::get('records/export', [RecordController::class, 'exportForm'])->name('records.export.form');
        Route::get('records/import', [RecordController::class, 'importForm'])->name('records.import.form');
        Route::post('records/import', [RecordController::class, 'import'])->name('records.import');
        Route::get('records/terms/autocomplete', [RecordController::class, 'autocompleteTerms'])->name('records.terms.autocomplete');
        Route::get('records/create/full', [RecordController::class, 'createFull'])->name('records.create.full');
        Route::get('records/{record}/full', [RecordController::class, 'showFull'])->name('records.showFull');
        Route::get('search', [RecordController::class, 'search'])->name('records.search');

        // Routes containers AVANT la route resource
        Route::post('records/container/insert', [RecordContainerController::class, 'store'])->name('record-container-insert');
        Route::post('records/container/remove', [RecordContainerController::class, 'destroy'])->name('record-container-remove');

        // Route resource principale (génère automatiquement show, create, store, edit, update, destroy)
        Route::resource('records', RecordController::class);

        // Routes imbriquées
        Route::resource('records.attachments', RecordAttachmentController::class);
        Route::post('attachments/upload-temp', [RecordAttachmentController::class, 'uploadTemp'])->name('attachments.upload-temp');

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
        Route::resource('taskstatus', TaskStatusController::class);
        Route::resource('tasktype', TaskTypeController::class);
        Route::resource('logs', LogController::class)->only(['index', 'show']);
        Route::resource('backups', BackupController::class);
        Route::resource('backups.files', BackupFileController::class);
        Route::resource('backups.plannings', BackupPlanningController::class);
    });



    Route::prefix('dollies')->group(function () {
        Route::get('/', [DollyController::class, 'index']);
        Route::post('create-with-communications', [DollyController::class, 'createWithCommunications'])->name('dolly.createWithCommunications');
        Route::resource('dolly', DollyController::class)->names('dolly');
        Route::get('action', [DollyActionController::class, 'index'])->name('dollies.action');
        Route::get('sort', [SearchdollyController::class, 'index'])->name('dollies-sort');
        Route::delete('{dolly}/remove-record/{record}', [DollyController::class, 'removeRecord'])->name('dolly.remove-record');
        Route::delete('{dolly}/remove-mail/{mail}', [DollyController::class, 'removeMail'])->name('dolly.remove-mail');
        Route::post('{dolly}/add-record', [DollyController::class, 'addRecord'])->name('dolly.add-record');
        Route::post('{dolly}/add-mail', [DollyController::class, 'addMail'])->name('dolly.add-mail');
        Route::post('{dolly}/add-communication', [DollyController::class, 'addCommunication'])->name('dolly.add-communication');
        Route::post('{dolly}/add-room', [DollyController::class, 'addRoom'])->name('dolly.add-room');
        Route::post('{dolly}/add-container', [DollyController::class, 'addContainer'])->name('dolly.add-container');
        Route::post('{dolly}/add-shelve', [DollyController::class, 'addShelve'])->name('dolly.add-shelve');
        Route::post('{dolly}/add-slip-record', [DollyController::class, 'addSlipRecord'])->name('dolly.add-slip-record');
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



    // Routes pour le module Workflow
    Route::prefix('workflows')->name('workflows.')->group(function () {
        // Routes pour les templates de workflow
        Route::resource('templates', WorkflowTemplateController::class);
        Route::post('templates/{template}/toggle-active', [WorkflowTemplateController::class, 'toggleActive'])->name('templates.toggle-active');
        Route::post('templates/{template}/duplicate', [WorkflowTemplateController::class, 'duplicate'])->name('templates.duplicate');

        // Routes supplémentaires pour la configuration JSON
        Route::get('templates/{template}/configuration/form', [WorkflowTemplateController::class, 'getConfigurationForForm'])->name('templates.configuration.form');
        Route::post('templates/{template}/configuration/sync', [WorkflowTemplateController::class, 'syncConfigurationWithSteps'])->name('templates.configuration.sync');

        // Routes pour les étapes de workflow
        Route::resource('templates.steps', WorkflowStepController::class)->shallow();
        Route::post('steps/{step}/assignments', [WorkflowStepController::class, 'storeAssignments'])->name('steps.assignments.store');
        Route::delete('steps/{step}/assignments/{assignment}', [WorkflowStepController::class, 'destroyAssignment'])->name('steps.assignments.destroy');
        Route::post('templates/{template}/steps/reorder', [WorkflowStepController::class, 'reorder'])->name('templates.steps.reorder');

        // Routes pour les instances de workflow
        Route::resource('instances', WorkflowInstanceController::class);
        // Autocomplete entités liées pour les instances
        Route::get('instances/entities/autocomplete', [WorkflowInstanceController::class, 'autocompleteEntity'])->name('instances.entities.autocomplete');
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
            return redirect()->route('workflows.dashboard');
        });
        Route::get('dashboard', [WorkflowInstanceController::class, 'dashboard'])->name('dashboard');













        // Routes pour les tâches liées au workflow
        Route::prefix('tasks')->name('tasks.')->group(function () {
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

            // Gestion des pièces jointes
            Route::post('{task}/attachment/{attachmentId}/remove', [TaskController::class, 'removeAttachment'])->name('removeAttachment');
            Route::get('{task}/attachment/{attachmentId}/download', [TaskController::class, 'downloadAttachment'])->name('download');

        Route::get('/supervision', [TaskController::class, 'supervision'])->name('supervision');
    });

    // Dashboard du module workflow
    Route::get('/', function () {
        return redirect()->route('workflows.dashboard');
    });
    Route::get('dashboard', [WorkflowInstanceController::class, 'dashboard'])->name('dashboard');
});

// Routes d'administration du Rate Limiting
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('rate-limit/dashboard', [RateLimitController::class, 'dashboard'])->name('rate-limit.dashboard');
    Route::get('rate-limit/user-stats', [RateLimitController::class, 'userStats'])->name('rate-limit.user-stats');
    Route::post('rate-limit/clear', [RateLimitController::class, 'clearLimits'])->name('rate-limit.clear');
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
    Route::resource('event-registrations', PublicEventRegistrationController::class)->names('event-registrations');

    // Gestion du contenu public
    Route::resource('news', PublicNewsController::class)->names('news');
    Route::resource('pages', PublicPageController::class)->names('pages');
    Route::resource('templates', PublicTemplateController::class)->names('templates');

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

// Routes MCP Web - Communication avec le serveur MCP depuis l'interface web
// MCP Proxy routes moved to api.php to avoid conflicts
// Uncomment if you need web-specific MCP routes
/*
Route::middleware(['auth'])->prefix('web/mcp')->name('web.mcp.')->group(function () {
    Route::post('reformulate-record', [App\Http\Controllers\McpProxyController::class, 'reformulateRecord'])
        ->name('reformulate-record');

    Route::get('status', [App\Http\Controllers\McpProxyController::class, 'checkMcpStatus'])
        ->name('status');

    Route::get('info', [App\Http\Controllers\McpProxyController::class, 'getMcpInfo'])
        ->name('info');
});
*/




