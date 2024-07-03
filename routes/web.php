<?php

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
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\AuthorContactController;
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
use App\Http\Controllers\TermCategoryController;
use App\Http\Controllers\TermEquivalentTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\TermTypeController;
use App\Http\Controllers\TermEquivalentController;
use App\Http\Controllers\TermRelatedController;
use App\Http\Controllers\TermTranslationController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RecordSupportController;
use App\Http\Controllers\CommunicationStatusController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\CommunicationRecordController;
use App\Http\Controllers\RecordStatusController;
use App\Models\ContainerProperty;
use App\Models\Transaction;

Auth::routes();

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::prefix('mails')->group(function () {
        Route::resource('file', MailController::class)->names('mails');
        Route::resource('batches', BatchController::class)->names('batch');
        Route::resource('authors.contacts', AuthorContactController::class)->names('author-contact');
        Route::resource('archiving', MailArchivingController::class)->names('mail-archiving');
        Route::resource('container', MailContainerController::class)->names('mail-container');
        Route::resource('send', MailSendController::class)->names('mail-send');
        Route::resource('received', MailReceivedController::class)->names('mail-received');
        Route::resource('authors', AuthorController::class)->names('mail-author');
        Route::resource('batch', BatchController::class);
        Route::resource('batch-received', BatchReceivedController::class);
        Route::resource('batch-send', BatchSendController::class);
        Route::resource('file.attachment', MailAttachmentController::class)->names('mail-attachment');
    });


    Route::resource('accessions', AccessionController::class);


    Route::prefix('communications')->group(function () {
        Route::resource('transactions', CommunicationController::class);
        Route::resource('transactions.records', CommunicationRecordController::class);
    });


    Route::prefix('repositories')->group(function () {
        Route::resource('records', RecordController::class);

        Route::get('search', [RecordController::class, 'search'])->name('records.search');
        Route::resource('authors', RecordAuthorController::class)->names('record-author');
    });


    Route::prefix('deposits')->group(function () {
        Route::resource('buildings', BuildingController::class);
        Route::resource('buildings.floors', floorController::class)->names('floors');
        Route::resource('rooms', RoomController::class);
        Route::resource('shelves', ShelfController::class);
        Route::resource('containers', ContainerController::class);
        Route::resource('trolleys', BuildingController::class);
    });

    Route::prefix('settings')->group(function () {
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
        Route::resource('record-statuses', RecordStatusController::class);
    });

    Route::prefix('tools')->group(function () {
        Route::resource('activities', ActivityController::class);
        Route::resource('retentions', RetentionController::class);
        Route::resource('communicabilities', CommunicabilityController::class);
        Route::resource('thesaurus', ContainerStatusController::class);
        Route::resource('organisations', OrganisationController::class);
        Route::resource('access', ContainerStatusController::class);
        Route::resource('terms', TermController::class);
        Route::resource('terms.term-related', TermRelatedController::class)->names('term-related');
        Route::resource('terms.term-equivalents', TermEquivalentController::class)->names('term-equivalents');
        Route::resource('terms.term-translations', TermTranslationController::class)->names('term-translations');
    });

    Route::get('/records/search', [RecordController::class, 'search'])->name('records.search');
    Route::get('/mails/search', [MailController::class, 'search'])->name('mails.search');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
