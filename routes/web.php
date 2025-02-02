<?php

use App\Http\Controllers\PDFController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskTypeController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MailSendController;
use App\Http\Controllers\MailReceivedController;
use App\Http\Controllers\MailArchiveController;
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
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\activityCommunicabilityController;
//use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\AuthorContactController;
use App\Http\Controllers\MailAuthorController;
use App\Http\Controllers\MailTransactionController;
use App\Http\Controllers\MailAuthorContactController;
use App\Http\Controllers\SettingController;
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
use App\Http\Controllers\OrganisationActiveController;
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
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SlipStatusController;
use App\Http\Controllers\SlipRecordController;
use App\Http\Controllers\SlipRecordAttachmentController;
use App\Http\Controllers\SlipController;
use App\Http\Controllers\SlipContainerController;
use App\Http\Controllers\SlipRecordContainerController;
use App\Http\Controllers\MailActionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOrganisationRoleController;
use App\Http\Controllers\DollyActionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserOrganisationController;
use App\Http\Controllers\SearchMailFeedbackController;
use App\Http\Controllers\SearchSlipController;
use App\Http\Controllers\UserRoleController;
use App\Models\ContainerProperty;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BulletinBoardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupFileController;
use App\Http\Controllers\BackupPlanningController;

Auth::routes();
//Route::post('/transferrings/slips/import', [SlipController::class, 'import'])->name('slips.import');
//Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
//Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::get('pdf/thumbnail/{id}', [PDFController::class, 'thumbnail'])->name('pdf.thumbnail');




/*


    <?php

// Routes publiques (sans authentification)
Route::prefix('archives')->group(function () {
    Route::get('/', [PublicArchiveController::class, 'index'])->name('archives.index');
    Route::get('records', [PublicRecordController::class, 'index'])->name('archives.records.index');
    Route::get('records/{record}', [PublicRecordController::class, 'show'])->name('archives.records.show');
    Route::get('pages', [PublicPageController::class, 'index'])->name('archives.pages.index');
    Route::get('pages/{page}', [PublicPageController::class, 'show'])->name('archives.pages.show');
    Route::get('news', [PublicNewsController::class, 'index'])->name('archives.news.index');
    Route::get('news/{news}', [PublicNewsController::class, 'show'])->name('archives.news.show');
    Route::get('events', [PublicEventController::class, 'index'])->name('archives.events.index');
    Route::get('events/{event}', [PublicEventController::class, 'show'])->name('archives.events.show');

    // Inscription utilisateur
    Route::get('register', [PublicUserController::class, 'create'])->name('archives.register');
    Route::post('register', [PublicUserController::class, 'store']);
});

// Routes pour utilisateurs connectés
Route::middleware(['auth', 'verified'])->prefix('archives')->group(function () {
    // Gestion du profil utilisateur
    Route::get('profile', [UserProfileController::class, 'edit'])->name('archives.profile.edit');
    Route::put('profile', [UserProfileController::class, 'update'])->name('archives.profile.update');

    // Demandes de documents
    Route::resource('requests', UserDocumentRequestController::class)
        ->except(['edit', 'update', 'destroy'])
        ->names([
            'index' => 'archives.requests.index',
            'create' => 'archives.requests.create',
            'store' => 'archives.requests.store',
            'show' => 'archives.requests.show'
        ]);

    // Réponses aux demandes
    Route::get('responses', [UserResponseController::class, 'index'])->name('archives.responses.index');
    Route::get('responses/{response}', [UserResponseController::class, 'show'])->name('archives.responses.show');

    // Historique de navigation
    Route::get('history', [UserBrowsingHistoryController::class, 'index'])->name('archives.history.index');
    Route::delete('history', [UserBrowsingHistoryController::class, 'clear'])->name('archives.history.clear');
});

// Routes administration
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Gestion des records
    Route::resource('records', AdminRecordController::class)->names('admin.records');
    Route::post('records/{record}/publish', [AdminRecordController::class, 'publish'])->name('admin.records.publish');
    Route::post('records/{record}/unpublish', [AdminRecordController::class, 'unpublish'])->name('admin.records.unpublish');

    // Gestion des réponses et pièces jointes
    Route::resource('responses', AdminResponseController::class)->names('admin.responses');
    Route::resource('responses.attachments', AdminResponseAttachmentController::class)
        ->except(['index'])
        ->names('admin.responses.attachments');

    // Gestion des demandes
    Route::resource('requests', AdminDocumentRequestController::class)->names('admin.requests');
    Route::post('requests/{request}/approve', [AdminDocumentRequestController::class, 'approve'])->name('admin.requests.approve');
    Route::post('requests/{request}/reject', [AdminDocumentRequestController::class, 'reject'])->name('admin.requests.reject');

    // Gestion des templates
    Route::resource('templates', AdminTemplateController::class)->names('admin.templates');
    Route::post('templates/{template}/activate', [AdminTemplateController::class, 'activate'])->name('admin.templates.activate');
    Route::post('templates/{template}/deactivate', [AdminTemplateController::class, 'deactivate'])->name('admin.templates.deactivate');

    // Gestion des pages
    Route::resource('pages', AdminPageController::class)->names('admin.pages');
    Route::post('pages/reorder', [AdminPageController::class, 'reorder'])->name('admin.pages.reorder');
    Route::post('pages/{page}/publish', [AdminPageController::class, 'publish'])->name('admin.pages.publish');

    // Gestion des actualités
    Route::resource('news', AdminNewsController::class)->names('admin.news');
    Route::post('news/{news}/publish', [AdminNewsController::class, 'publish'])->name('admin.news.publish');

    // Gestion des événements
    Route::resource('events', AdminEventController::class)->names('admin.events');

    // Gestion des utilisateurs
    Route::resource('users', AdminUserController::class)->names('admin.users');
    Route::post('users/{user}/approve', [AdminUserController::class, 'approve'])->name('admin.users.approve');
    Route::post('users/{user}/block', [AdminUserController::class, 'block'])->name('admin.users.block');

    // Statistiques et rapports
    Route::get('statistics', [AdminStatisticsController::class, 'index'])->name('admin.statistics.index');
    Route::get('reports/downloads', [AdminReportController::class, 'downloads'])->name('admin.reports.downloads');
    Route::get('reports/requests', [AdminReportController::class, 'requests'])->name('admin.reports.requests');
    Route::get('reports/users', [AdminReportController::class, 'users'])->name('admin.reports.users');
});


*/












Route::group(['middleware' => 'auth'], function () {


    //Route::get('/switch-organisation/{organisation}', 'OrganisationController@switchOrganisation')->name('switch.organisation');
    Route::post('/switch-organisation', [OrganisationController::class, 'switchOrganisation'])->name('switch.organisation');
    Route::get('/', [mailReceivedController::class, 'index']);


    Route::prefix('ai')->group(function () {
        Route::get('/', [PromptController::class, 'index']);
        Route::resource('prompts', PromptController::class);
        Route::patch('prompts/{prompt}/archive', [PromptController::class, 'archive'])->name('prompts.archive');
        Route::patch('prompts/{prompt}/toggle-draft', [PromptController::class, 'toggleDraft'])->name('prompts.toggle-draft');
        Route::patch('prompts/{prompt}/toggle-public', [PromptController::class, 'togglePublic'])->name('prompts.toggle-public');
        Route::resource('agents', AgentController::class);
        Route::patch('agents/{agent}/toggle-status', [AgentController::class, 'toggleStatus'])->name('agents.toggle-status');
        Route::patch('agents/{agent}/toggle-visibility', [AgentController::class, 'toggleVisibility'])->name('agents.toggle-visibility');
    });


    Route::prefix('bulletin-board')->group(function () {
        Route::resource('/', BulletinBoardController::class)->names('bulletin-boards');
        Route::resource('events', EventController::class)->except(['index']);
        Route::resource('attachments', BulletinBoardController::class)->except(['index']);
        Route::post('administrators/{user}', [BulletinBoardController::class, 'addAdministrator'])->name('bulletin-boards.administrators.add');
        Route::delete('administrators/{user}', [BulletinBoardController::class, 'removeAdministrator'])->name('bulletin-boards.administrators.remove');
        Route::post('organisations/{organisation}', [BulletinBoardController::class, 'addOrganisation'])->name('bulletin-boards.organisations.add');
        Route::delete('organisations/{organisation}', [BulletinBoardController::class, 'removeOrganisation'])->name('bulletin-boards.organisations.remove');
    });



    Route::prefix('mails')->group(function () {
        Route::post('advanced', [SearchMailController::class, 'advanced'])->name('mails.advanced');
        Route::get('advanced/form', [SearchMailController::class, 'form'])->name('mails.advanced.form');
        Route::resource('authors.contacts', MailAuthorContactController::class)->names('author-contact');
        Route::resource('archives', MailArchiveController::class)->names('mail-archive');
        Route::resource('container', MailContainerController::class)->names('mail-container');
        Route::resource('send', MailSendController::class)->names('mail-send');
        Route::get('feedback', [SearchMailFeedbackController::class, 'index'])->name('mail-feedback');
        Route::resource('received', MailReceivedController::class)->names('mail-received');
        Route::resource('authors', MailAuthorController::class)->names('mail-author');
        Route::resource('file.attachment', MailAttachmentController::class)->names('mail-attachment');
        Route::resource('typologies', MailTypologyController::class);
        Route::get('archived', [MailArchiveController::class, 'archived'])->name('mails.archived');
        Route::resource('batch', BatchController::class)->names('batch');
        Route::resource('batches.mail', BatchMailController::class)->names('batch.mail');
        Route::resource('batch-received', BatchReceivedController::class)->names('batch-received');
        Route::resource('batch-send', BatchSendController::class)->names('batch-send');
        Route::get('batch-received/logs', [BatchReceivedController::class, 'logs'] )->name('batch-received-log');
        Route::get('batch-send/logs', [BatchSendController::class, 'logs'] )->name('batch-send-log');
        Route::post('mail-transaction/export', [MailTransactionController::class, 'export'])->name('mail-transaction.export');
        Route::post('mail-transaction/print', [MailTransactionController::class, 'print'])->name('mail-transaction.print');
        Route::get('search', [SearchController::class, 'index'])->name('mails.search');
        Route::get('sort', [SearchMailController::class, 'index'])->name('mails.sort');
        Route::get('select', [SearchMailController::class, 'date'])->name('mail-select-date');
        Route::get('InProgress', [MailReceivedController::class, 'inprogress'])->name('mails.inprogress');
        Route::get('approve', [MailReceivedController::class, 'approve'])->name('mails.approve');
        Route::get('feedback', [SearchMailFeedbackController::class, 'index'])->name('mails.feedback');
        Route::get('/mail-attachment/{id}/preview', [MailAttachmentController::class, 'preview'])->name('mail-attachment.preview');
    });


    Route::prefix('communications')->group(function () {
        Route::get('/', [CommunicationController::class, 'index']);
        Route::get('print', [CommunicationController::class, 'print'])->name('communications.print');
        Route::post('add-to-cart', [CommunicationController::class, 'addToCart'])->name('communications.addToCart');
        Route::get('export', [CommunicationController::class, 'export'])->name('communications.export');
        // Routes de recherche et tri pour les réservations (à placer AVANT les routes resource)
        Route::get('reservations/sort', [SearchReservationController::class, 'index'])->name('reservations-sort');
        Route::get('reservations/select', [SearchReservationController::class, 'date'])->name('reservations-select-date');
        // Route::get('reservations/approved', [ReservationController::class, 'approved'])->name('reservations-approved');
        Route::post('reservations/approved', [ReservationController::class, 'approved'])->name('reservations-approved');
        // Routes resource
        Route::resource('transactions', CommunicationController::class);
        Route::resource('transactions.records', CommunicationRecordController::class);
        Route::resource('reservations', ReservationController::class);
        Route::resource('reservations.records', ReservationRecordController::class);

        Route::get('transactions/return', [CommunicationController::class, 'returnEffective'])->name('return-effective');
        Route::get('transactions/cancel', [CommunicationController::class, 'returnCancel'])->name('return-cancel');
        Route::get('transmission', [CommunicationController::class, 'transmission'])->name('record-transmission');
        Route::get('transactions/record/return', [CommunicationRecordController::class, 'returnEffective'])->name('record-return-effective');
        Route::get('transactions/record/cancel', [CommunicationRecordController::class, 'returnCancel'])->name('record-return-cancel');

        Route::get('sort', [SearchCommunicationController::class, 'index'])->name('communications-sort');
        Route::get('select', [SearchCommunicationController::class, 'date'])->name('communications-select-date');

        // Advanced search routes
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
        //Route::post('/records/export/{format}', [RecordController::class, 'export'])->name('records.export');
        Route::post('records/container/insert', [RecordContainerController::class, 'store'])->name('record-container-insert');
        Route::post('records/container/remove', [RecordContainerController::class, 'destroy'])->name('record-container-remove');
        Route::get('records/import', [RecordController::class, 'importForm'])->name('records.import.form');
        Route::post('records/import', [RecordController::class, 'import'])->name('records.import');
        Route::resource('records', RecordController::class);
        Route::resource('records.attachments', RecordAttachmentController::class);
        Route::get('search', [RecordController::class, 'search'])->name('records.search');
        Route::resource('authors', RecordAuthorController::class)->names('record-author');
        Route::resource('records.child', RecordChildController::class)->names('record-child');
        Route::get('recordtotransfer', [lifeCycleController::class, 'recordToTransfer'])->name('records.totransfer');
        Route::get('recordtosort', [lifeCycleController::class, 'recordToSort'])->name('records.tosort');
        Route::get('recordtoeliminate', [lifeCycleController::class, 'recordToEliminate'])->name('records.toeliminate');
        Route::get('recordtokeep', [lifeCycleController::class, 'recordToKeep'])->name('records.tokeep');
        Route::get('recordtoretain', [lifeCycleController::class, 'recordToRetain'])->name('records.toretain');
        Route::get('recordtostore', [lifeCycleController::class, 'recordToStore'])->name('records.tostore');

        Route::post('advanced', [SearchRecordController::class, 'advanced'])->name('records.advanced');
        Route::get('advanced/form', [SearchRecordController::class, 'form'])->name('records.advanced.form');
        Route::get('search', [SearchController::class, 'index'])->name('records.search');
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
        // Route::delete('slipRecordAttachment/{id}', [SlipRecordAttachmentController::class, 'delete'])->name('slipRecordAttachment.delete');
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
//        Route::resource('mail-priority', MailPriorityController::class);
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
        // A revoir *** Route::resource('dolly-mail-transactions', DollyMailTransactionController::class);
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
        //Route::post('barcode', [BarcodeController::class,'generate'])->name('barcode.generate');
        Route::post('/barcodes/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
        Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::post('/barcodes/generate', [BarcodeController::class, 'generate'])->name('barcode.generate');
        Route::resource('terms.term-related', TermRelatedController::class)->names('term-related');
        Route::resource('terms.term-equivalents', TermEquivalentController::class)->names('term-equivalents');
        Route::resource('terms.term-translations', TermTranslationController::class)->names('term-translations');
    });

    Route::prefix('tasks')->group(function () {
        Route::resource('/', TaskController::class)->names('tasks');
        Route::get('myTasks', [TaskController::class, 'myTasks'])->name('tasks.myTasks');
        Route::get('supervision', [TaskController::class, 'supervision'])->name('tasks.supervision');
        Route::delete('{task}/attachments/{attachment}', [TaskController::class, 'removeAttachment'])->name('tasks.remove-attachment');
        Route::post('{task}/download/{attachment}', [TaskController::class, 'downloadAttachment'])->name('tasks.download');
        Route::get('attachments/{id}/download', [MailAttachmentController::class, 'download'])->name('attachments.download');
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

});




