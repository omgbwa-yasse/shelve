<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\TransactionSendController;
use App\Http\Controllers\TransactionReceivedController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchReceivedController;
use App\Http\Controllers\BatchSendController;
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

    Route::get('/setting', function () {
        return view('settings');
    })->name('setting');

    Route::resource('mail', MailController::class);
    Route::prefix('mail')->group(function () {
        Route::resource('subject', MailSubjectController::class);
        Route::resource('batch', BatchController::class);
    });

    Route::prefix('batch')->group(function () {
        Route::prefix('received')->group(function () {
            Route::resource('/', BatchReceivedController::class)->names('batch-received');;
        });

        Route::prefix('send')->group(function () {
            Route::resource('/', BatchSendController::class)->names('batch-send');
        });
    });

    Route::prefix('transaction')->group(function () {
        Route::prefix('send')->group(function () {
            Route::resource('/', TransactionSendController::class)->names('mail-received');
        });

        Route::prefix('received')->group(function () {
            Route::resource('/', TransactionReceivedController::class)->names('mail-send');
        });
    });

    Route::resource('repository', RepositoryController::class);
    Route::resource('communication', CommunicationController::class);
    Route::resource('accession', AccessionController::class);
    Route::resource('tool', ToolsController::class);
    Route::resource('setting', SettingController::class);
    Route::resource('localisation', LocalisationController::class);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
