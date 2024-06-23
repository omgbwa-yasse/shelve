<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MailSendController;
use App\Http\Controllers\MailReceivedController;
use App\Http\Controllers\MailArchivingController;
use App\Http\Controllers\MailContainerController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchReceivedController;
use App\Http\Controllers\BatchSendController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\AuthorContactController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\TestController;
use App\Models\Transaction;

Auth::routes();

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/setting', function () {
        return view('settings');
    })->name('setting');

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

    });

    Route::resource('repositories', RepositoryController::class);
    Route::resource('communications', CommunicationController::class);
    Route::resource('accessions', AccessionController::class);
    Route::resource('tools', ToolsController::class);
    Route::resource('settings', SettingController::class);
    Route::resource('localisations', LocalisationController::class);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
