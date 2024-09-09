<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MailSendController;
use App\Http\Controllers\MailReceivedController;
use App\Http\Controllers\MailArchivingController;
use App\Http\Controllers\MailAttachmentController;
use App\Http\Controllers\MailContainerController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchReceivedController;
use App\Http\Controllers\BatchSendController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\RecordAuthorController;
use App\Http\Controllers\RecordAttachmentController;
use App\Http\Controllers\AccessionController;
//use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\AuthorContactController;
use App\Http\Controllers\MailAuthorController;
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
use App\Http\Controllers\CommunicabilityController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationRoomController;
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
use App\Http\Controllers\DollyController;
use App\Http\Controllers\SlipStatusController;
use App\Http\Controllers\SlipRecordController;
use App\Http\Controllers\SlipController;
use App\Http\Controllers\MailActionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DollyActionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserOrganisationController;
use App\Http\Controllers\SearchMailFeedbackController;
use App\Http\Controllers\SearchSlipController;
use App\Http\Controllers\UserRoleController;
use App\Models\ContainerProperty;
use App\Http\Controllers\ReportController;


Auth::routes();
Route::post('/transferrings/slips/import', [SlipController::class, 'import'])->name('slips.import');
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::get('/tasks/myTasks', [TaskController::class, 'myTasks'])->name('tasks.myTasks');
Route::get('/tasks/supervision', [TaskController::class, 'supervision'])->name('tasks.supervision');
Route::get('/mail-attachment/{id}/preview', [MailAttachmentController::class, 'preview'])->name('mail-attachment.preview');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/statistics/mails', [ReportController::class, 'statisticsMails'])->name('report.statistics.mails');
    Route::get('/statistics/repositories', [ReportController::class, 'statisticsRepositories'])->name('report.statistics.repositories');
    Route::get('/statistics/communications', [ReportController::class, 'statisticsCommunications'])->name('report.statistics.communications');
    Route::get('/statistics/transferrings', [ReportController::class, 'statisticsTransferrings'])->name('report.statistics.transferrings');
    Route::get('/statistics/deposits', [ReportController::class, 'statisticsDeposits'])->name('report.statistics.deposits');
    Route::get('/statistics/tools', [ReportController::class, 'statisticsTools'])->name('report.statistics.tools');
    Route::get('/statistics/dollies', [ReportController::class, 'statisticsDollies'])->name('report.statistics.dollies');

    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('report.dashboard');


    Route::prefix('mails')->group(function () {
        Route::resource('file', MailController::class)->names('mails');
        Route::resource('authors.contacts', MailAuthorContactController::class)->names('author-contact');
        Route::resource('archiving', MailArchivingController::class)->names('mail-archiving');
        Route::resource('container', MailContainerController::class)->names('mail-container');
        Route::resource('send', MailSendController::class)->names('mail-send');
        Route::get('feedback', [SearchMailFeedbackController::class, 'index'])->name('mail-feedback');
        Route::resource('received', MailReceivedController::class)->names('mail-received');
        Route::resource('authors', MailAuthorController::class)->names('mail-author');
        Route::resource('file.attachment', MailAttachmentController::class)->names('mail-attachment');
        Route::resource('typologies', MailTypologyController::class);
        Route::get('archived', [MailController::class, 'archived'])->name('mails.archived');

        // Les parapheurs
        Route::resource('batch', BatchController::class)->names('batch');
        Route::resource('batches.mail', BatchMailController::class)->names('batch.mail');
        Route::resource('batch-received', BatchReceivedController::class)->names('batch-received');
        Route::resource('batch-send', BatchSendController::class)->names('batch-send');
    });

    Route::prefix('communications')->group(function () {
        Route::resource('transactions', CommunicationController::class);
        Route::resource('transactions.records', CommunicationRecordController::class);
        Route::resource('reservations', ReservationController::class);
        Route::resource('reservations.records', ReservationRecordController::class);
        Route::get('sort', [SearchCommunicationController::class, 'index'])->name('communications-sort');
        Route::get('select', [SearchCommunicationController::class, 'date'])->name('communications-select-date');
        Route::get('reservations.sort', [SearchReservationController::class, 'index'])->name('reservations-sort');
        Route::get('reservations.select', [SearchReservationController::class, 'date'])->name('reservations-select-date');
    });

    Route::prefix('repositories')->group(function () {
        Route::get('/records/export/{format}', [RecordController::class, 'export'])->name('records.export');
        Route::get('/records/import', [RecordController::class, 'importForm'])->name('records.import.form');
        Route::post('/records/import', [RecordController::class, 'import'])->name('records.import');
        Route::resource('records', RecordController::class);
        Route::resource('records.attachments', RecordAttachmentController::class);
        Route::get('search', [RecordController::class, 'search'])->name('records.search');
        Route::resource('authors', RecordAuthorController::class)->names('record-author');
        Route::resource('records.child', RecordChildController::class)->names('record-child');
        Route::get('totransfer', [lifeCycleController::class, 'recordToTransfer'])->name('records.totransfer');
        Route::get('tosort', [lifeCycleController::class, 'recordToSort'])->name('records.tosort');
        Route::get('toeliminate', [lifeCycleController::class, 'recordToEliminate'])->name('records.toeliminate');
        Route::get('tokeep', [lifeCycleController::class, 'recordToKeep'])->name('records.tokeep');
        Route::get('toretain', [lifeCycleController::class, 'recordToRetain'])->name('records.toretain');

    });

    Route::prefix('transferrings')->group(function () {
        Route::get('/slips/import', [SlipController::class, 'importForm'])->name('slips.import.form');
        Route::get('/slips/export/{format}', [SlipController::class, 'export'])->name('slips.export');
//        Route::post('/slips/import/{format}', [SlipController::class, 'import'])->name('slips.import');
        Route::resource('slips', SlipController::class);
        Route::resource('slips.records', SlipRecordController::class);
        Route::get('slip.sort', [SearchSlipController::class, 'index'])->name('slips-sort');
        Route::get('slip.select', [SearchSlipController::class, 'date'])->name('slips-select-date');
        Route::get('organisation-select', [SearchSlipController::class, 'organisation'])->name('slips-select-organisation');
    });

    Route::prefix('deposits')->group(function () {
        Route::resource('buildings', BuildingController::class);
        Route::resource('buildings.floors', FloorController::class)->names('floors');
        Route::resource('rooms', RoomController::class);
        Route::resource('shelves', ShelfController::class);
        Route::resource('containers', ContainerController::class);
        Route::resource('trolleys', BuildingController::class);
    });

    Route::prefix('settings')->group(function () {
        Route::resource('user-organisations', UserOrganisationController::class);
        Route::resource('user-roles', UserRoleController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::resource('role_permissions', RolePermissionController::class);
        Route::resource('mail-typology', MailTypologyController::class);
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
    });

    Route::prefix('dollies')->group(function () {
        Route::resource('dolly', DollyController::class)->names('dolly');
        Route::get('/action', [DollyActionController::class, 'index'])->name('dollies.action');
        Route::get('sort', [SearchdollyController::class, 'index'])->name('dollies-sort');
    });

    Route::prefix('tools')->group(function () {
        Route::resource('activities', ActivityController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('tools/activities/organigram', [ActivityController::class, 'organigram'])->name('activities.organigram');
        Route::resource('retentions', RetentionController::class);
        Route::resource('communicabilities', CommunicabilityController::class);
        Route::resource('thesaurus', ContainerStatusController::class);
        Route::resource('organisations', OrganisationController::class);
        Route::resource('organisations.rooms', OrganisationRoomController::class);
        Route::resource('access', ContainerStatusController::class);
        Route::resource('terms', TermController::class);
        Route::resource('terms.term-related', TermRelatedController::class)->names('term-related');
        Route::resource('terms.term-equivalents', TermEquivalentController::class)->names('term-equivalents');
        Route::resource('terms.term-translations', TermTranslationController::class)->names('term-translations');
    });

    Route::resource('tasks', TaskController::class);
    Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'removeAttachment'])->name('tasks.remove-attachment');
    Route::post('/tasks/{task}/download/{attachment}', [TaskController::class, 'downloadAttachment'])->name('tasks.download');

    Route::get('/mails/search', [SearchController::class, 'index'])->name('mails.search');
    Route::get('/mails/sort', [SearchMailController::class, 'index'])->name('mails.sort');
    Route::get('/mails/select', [SearchMailController::class, 'date'])->name('mail-select-date');

    Route::get('/repositories/search', [SearchController::class, 'index'])->name('records.search');
    Route::get('/repositories/sort', [SearchRecordController::class, 'index'])->name('records.sort');
    Route::get('/repositories/select', [SearchRecordController::class, 'date'])->name('record-select-date');
    Route::get('/repositories/word', [SearchRecordController::class, 'selectWord'])->name('record-select-word');
    Route::get('/repositories/activity', [SearchRecordController::class, 'selectActivity'])->name('record-select-activity');
    Route::get('/repositories/building', [SearchRecordController::class, 'selectBuilding'])->name('record-select-building');
    Route::get('/repositories/last', [SearchRecordController::class, 'selectLast'])->name('record-select-last');
    Route::get('/repositories/floor', [SearchRecordController::class, 'selectFloor'])->name('record-select-floor');
    Route::get('/repositories/container', [SearchRecordController::class, 'selectContainer'])->name('record-select-container');
    Route::get('/repositories/room', [SearchRecordController::class, 'selectRoom'])->name('record-select-room');
    Route::get('/repositories/shelve', [SearchRecordController::class, 'selectShelve'])->name('record-select-shelve');

    Route::get('/transferrings/sort', [SlipController::class, 'sort'])->name('slips.sort');
    Route::get('/mails/InProgress', [MailReceivedController::class, 'inprogress'])->name('mails.inprogress');
    Route::get('/mails/approve', [MailReceivedController::class, 'approve'])->name('mails.approve');
    Route::get('/mails/feedback', [SearchMailFeedbackController::class, 'index'])->name('mails.feedback');
    Route::get('/transferrings/search', [SearchController::class, 'index'])->name('transferrings.search');

    Route::get('attachments/{id}/download', [MailAttachmentController::class, 'download'])->name('attachments.download');
});




