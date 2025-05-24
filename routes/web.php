<?php

use App\Http\Controllers\BulletinBoardAdminController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailSendController;
use App\Http\Controllers\MailReceivedController;
use App\Http\Controllers\MailArchiveController;
use App\Http\Controllers\MailOutgoingController;
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
use App\Http\Controllers\RoomController;
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
use App\Http\Controllers\TermCategoryController;
use App\Http\Controllers\TermEquivalentTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\TermTypeController;
use App\Http\Controllers\TermEquivalentController;
use App\Http\Controllers\TermRelatedController;
use App\Http\Controllers\TermTranslationController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\lifeCycleController;
use App\Http\Controllers\RecordChildController;
use App\Http\Controllers\RecordSupportController;
use App\Http\Controllers\CommunicationStatusController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\SearchCommunicationController;
use App\Http\Controllers\CommunicationRecordController;
use App\Http\Controllers\ReservationStatusController;
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
use App\Http\Controllers\MailIncomingController;
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
use App\Http\Controllers\SettingValueController;
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
use App\Http\Controllers\PublicSearchLogController;


// AI related controllers
use App\Http\Controllers\AiActionController;
use App\Http\Controllers\AiActionBatchController;
use App\Http\Controllers\AiActionTypeController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiChatMessageController;
use App\Http\Controllers\AiFeedbackController;
use App\Http\Controllers\AiIntegrationController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiPromptTemplateController;
use App\Http\Controllers\AiResourceController;
use App\Http\Controllers\AiTrainingDataController;

use App\Http\Controllers\PortalEventController;
use App\Http\Controllers\PortalNewsController;
use App\Http\Controllers\PortalPageController;

Auth::routes();

Route::get('pdf/thumbnail/{id}', [PDFController::class, 'thumbnail'])->name('pdf.thumbnail');

Route::group(['middleware' => 'auth'], function () {
    Route::prefix('api')->group(function () {
        Route::get('/authors', [AuthorController::class, 'indexApi']);
        Route::post('/authors', [AuthorController::class, 'storeApi']);
        Route::get('/author-types', [AuthorController::class, 'authorTypesApi']);
    });

    Route::post('/switch-organisation', [OrganisationController::class, 'switchOrganisation'])->name('switch.organisation');
    Route::get('/', [mailReceivedController::class, 'index']);

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

        Route::resource('send', MailSendController::class)->names('mail-send');
        Route::post('send/transfer', [MailSendController::class, 'transfer'])->name('mail-send.transfer');

        Route::resource('outgoing', MailOutgoingController::class)->names('mail-outgoing');

        Route::resource('received', MailReceivedController::class)->names('mail-received');

        Route::resource('incoming', MailIncomingController::class)->names('mail-incoming');

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
        Route::post('mail-transaction/print', [MailTransactionController::class, 'print'])->name('mail-transaction.print');

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
    });


    Route::get('/api/dollies', [DollyController::class, 'apiList']);
    Route::post('/api/dollies', [DollyController::class, 'apiCreate']);


    // Gestion des chariots en AJAX, les routes
    Route::post('/dolly-handler/create', [DollyHandlerController::class, 'addDolly']);
    Route::get('/dolly-handler/list', [DollyHandlerController::class, 'list']);
    Route::post('/dolly-handler/add-items', [DollyHandlerController::class, 'addItems']);
    Route::delete('/dolly-handler/remove-items', [DollyHandlerController::class, 'removeItems']);
    Route::delete('/dolly-handler/clean', [DollyHandlerController::class, 'clean']);
    Route::delete('/dolly-handler/{dolly_id}', [DollyHandlerController::class, 'deleteDolly']);



    Route::prefix('communications')->group(function () {
        Route::get('/', [CommunicationController::class, 'index']);
        Route::get('print', [CommunicationController::class, 'print'])->name('communications.print');
        Route::post('add-to-cart', [CommunicationController::class, 'addToCart'])->name('communications.addToCart');
        Route::get('export', [CommunicationController::class, 'export'])->name('communications.export');
        Route::get('reservations/sort', [SearchReservationController::class, 'index'])->name('reservations-sort');
        Route::get('reservations/select', [SearchReservationController::class, 'date'])->name('reservations-select-date');
        Route::post('reservations/approved', [ReservationController::class, 'approved'])->name('reservations-approved');
        Route::resource('transactions', CommunicationController::class);
        Route::resource('transactions.records', CommunicationRecordController::class);
        Route::resource('reservations', ReservationController::class);
        Route::resource('reservations.records', ReservationRecordController::class);
        Route::get('return', [CommunicationController::class, 'returnEffective'])->name('return-effective');
        Route::get('cancel', [CommunicationController::class, 'returnCancel'])->name('return-cancel');
        Route::get('transmission', [CommunicationController::class, 'transmission'])->name('record-transmission');
        Route::get('record/return', [CommunicationRecordController::class, 'returnEffective'])->name('record-return-effective');
        Route::get('record/cancel', [CommunicationRecordController::class, 'returnCancel'])->name('record-return-cancel');
        Route::get('sort', [SearchCommunicationController::class, 'index'])->name('communications-sort');
        Route::get('select', [SearchCommunicationController::class, 'date'])->name('communications-select-date');
        Route::get('/advanced', [SearchCommunicationController::class, 'form'])->name('communications.advanced.form');
        Route::post('/advanced', [SearchCommunicationController::class, 'advanced'])->name('search.communications.advanced');

    });


    Route::prefix('repositories')->group(function () {
        Route::post('/slips/store', [SlipController::class, 'storetransfert'])->name('slips.storetransfert');
        Route::get('/', [RecordController::class, 'index']);
        Route::get('shelve', [SearchRecordController::class, 'selectShelve'])->name('record-select-shelve');
        Route::post('dolly/create-with-records', [DollyController::class, 'createWithRecords'])->name('dolly.createWithRecords');
        Route::get('records/exportButton', [RecordController::class, 'exportButton'])->name('records.exportButton');
        Route::post('records/print', [RecordController::class, 'printRecords'])->name('records.print');
        Route::post('records/export', [RecordController::class, 'export'])->name('records.export');
        Route::get('records/export', [RecordController::class, 'exportForm'])->name('records.export.form');
        Route::post('records/container/insert', [RecordContainerController::class, 'store'])->name('record-container-insert');
        Route::post('records/container/remove', [RecordContainerController::class, 'destroy'])->name('record-container-remove');
        Route::get('records/import', [RecordController::class, 'importForm'])->name('records.import.form');
        Route::post('records/import', [RecordController::class, 'import'])->name('records.import');
        Route::resource('records', RecordController::class);
        Route::get('records/create/full', [RecordController::class, 'createFull'])->name('records.create.full');
        Route::resource('records.attachments', RecordAttachmentController::class);
        Route::get('search', [RecordController::class, 'search'])->name('records.search');

        Route::resource('authors', RecordAuthorController::class)->names('record-author');
        Route::get('authors/list', [RecordAuthorController::class, 'list'])->name('record-author.list');

        Route::resource('records.child', RecordChildController::class)->names('record-child');
        Route::get('recordtotransfer', [lifeCycleController::class, 'recordToTransfer'])->name('records.totransfer');
        Route::get('recordtosort', [lifeCycleController::class, 'recordToSort'])->name('records.tosort');
        Route::get('recordtoeliminate', [lifeCycleController::class, 'recordToEliminate'])->name('records.toeliminate');
        Route::get('recordtokeep', [lifeCycleController::class, 'recordToKeep'])->name('records.tokeep');
        Route::get('recordtoretain', [lifeCycleController::class, 'recordToRetain'])->name('records.toretain');
        Route::get('recordtostore', [lifeCycleController::class, 'recordToStore'])->name('records.tostore');
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
        Route::resource('values', SettingValueController::class)->names('settings.values');
        Route::resource('categories', SettingCategoryController::class)->names('settings.categories');

        Route::get('categories/{id}/settings', [SettingCategoryController::class, 'getSettings'])->name('settings.categories.settings');

        Route::get('activities/export/excel', [ActivityController::class, 'exportExcel'])->name('activities.export.excel');
        Route::get('activities/export/pdf', [ActivityController::class, 'exportPdf'])->name('activities.export.pdf');
        Route::get('organisations/export/excel', [OrganisationController::class, 'exportExcel'])->name('organisations.export.excel');
        Route::get('organisations/export/pdf', [OrganisationController::class, 'exportPdf'])->name('organisations.export.pdf');
        Route::get('users', [UserController::class, 'index'] );
        Route::resource('user-organisation-role', UserOrganisationRoleController::class);
        Route::resource('user-roles', UserRoleController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::resource('role_permissions', RolePermissionController::class);
        Route::resource('mail-typology', MailTypologyController::class);
        Route::resource('mail-priority', MailPriorityController::class);
        Route::resource('container-status', ContainerStatusController::class);
        Route::resource('container-property', ContainerPropertyController::class);
        Route::resource('sorts', SortController::class);
        Route::resource('term-categories', TermCategoryController::class);
        Route::resource('term-equivalent-types', TermEquivalentTypeController::class);
        Route::resource('term-types', TermTypeController::class);
        Route::resource('languages', LanguageController::class);
        Route::resource('record-supports', RecordSupportController::class);
        Route::resource('communication-status', CommunicationStatusController::class);
        Route::resource('reservation-status', ReservationStatusController::class);
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
        Route::resource('thesaurus', ContainerStatusController::class);
        Route::resource('organisations', OrganisationController::class);
        Route::resource('organisations.rooms', OrganisationRoomController::class);
        Route::resource('organisations.activities', OrganisationActivityController::class);
        Route::resource('access', ContainerStatusController::class);
        Route::resource('terms', TermController::class);
        Route::get('barcode', [BarcodeController::class,'create'])->name('barcode.create');
        Route::post('/barcodes/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
        Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::post('/barcodes/generate', [BarcodeController::class, 'generate'])->name('barcode.generate');
        Route::resource('terms.term-related', TermRelatedController::class)->names('term-related');
        Route::resource('terms.term-equivalents', TermEquivalentController::class)->names('term-equivalents');
        Route::resource('terms.term-translations', TermTranslationController::class)->names('term-translations');
    });



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

    Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');



    // Admin routes
    Route::prefix('admin/ai')->middleware(['auth', 'admin'])->group(function () {
        Route::resource('models', AiModelController::class);
        Route::resource('action-types', AiActionTypeController::class);
        Route::resource('prompt-templates', AiPromptTemplateController::class);
        Route::resource('integrations', AiIntegrationController::class);
        Route::resource('jobs', AiJobController::class)->only(['index', 'show', 'destroy']);
        Route::resource('training-data', AiTrainingDataController::class);
    });

    // User accessible routes
    Route::prefix('ai')->middleware(['auth'])->group(function () {
        Route::resource('chats', AiChatController::class);
        Route::resource('chats.messages', AiChatMessageController::class)->shallow();
        Route::resource('interactions', AiInteractionController::class)->only(['index', 'show']);
        Route::resource('actions', AiActionController::class)->only(['index', 'show', 'update']);
        Route::resource('action-batches', AiActionBatchController::class);
        Route::resource('feedback', AiFeedbackController::class)->only(['store', 'update']);
        Route::resource('resources', AiResourceController::class)->only(['index', 'show']);
    });

    // Portal
    Route::prefix('portal')->middleware(['auth', 'admin'])->group(function () {

    });


    Route::prefix('public')->group(function () {
        // User related routes
        Route::resource('users', PublicUserController::class)->names('public.users');

        // Chat related routes
        Route::resource('chats', PublicChatController::class)->names('public.chats');
        Route::resource('chats.messages', PublicChatMessageController::class)->shallow();
        Route::resource('chat-participants', PublicChatParticipantController::class);

        // Events related routes
        Route::resource('events', PublicEventController::class)->names('public.events');
        Route::resource('event-registrations', PublicEventRegistrationController::class)->names('public.event-registrations');

        // Content related routes
        Route::resource('news', PublicNewsController::class)->names('public.news');
        Route::resource('pages', PublicPageController::class)->names('public.pages');
        Route::resource('templates', PublicTemplateController::class)->names('public.templates');

        // Document related routes
        Route::resource('document-requests', PublicDocumentRequestController::class)->names('public.document-requests');
        Route::resource('records', PublicRecordController::class)->names('public.records');
        Route::resource('responses', PublicResponseController::class)->names('public.responses');
        Route::resource('response-attachments', PublicResponseAttachmentController::class)->names('public.response-attachments');

        // Feedback and search
        Route::resource('feedback', PublicFeedbackController::class)->names('public.feedback');
        Route::resource('search-logs', PublicSearchLogController::class)->only(['index', 'show'])->names('public.search-logs');
    });



});

// OPAC Route - SPA React Application
Route::get('/opac{any}', function () {
    return view('opac');
})->where('any', '.*');




