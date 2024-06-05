<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\TransactionSendController;
use App\Http\Controllers\TransactionReceivedController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\MailSubjectController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocalisationController;
use App\Models\Transaction;

Auth::routes();

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/settings', function () {
        return view('settings');
    })->name('setting');

    Route::resource('mails', MailController::class);
    Route::prefix('mails')->group(function () {
        Route::resource('subject', MailSubjectController::class);
        Route::resource('batch', BatchController::class);
    });
    Route::prefix('transactions')->group(function () {
        Route::resource('send', TransactionSendController::class);
        Route::resource('received', TransactionReceivedController::class);
    });
    Route::resource('repositories', RepositoryController::class);
    Route::resource('communications', CommunicationController::class);
    Route::resource('accessions', AccessionController::class);
    Route::resource('tools', ToolsController::class);
    Route::resource('setting', SettingController::class);
    Route::resource('localisations', LocalisationController::class);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
