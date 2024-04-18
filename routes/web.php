<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/settings', function () {
    return view('settings');
})->name('setting');


use App\Http\Controllers\MailController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocalisationController;

Route::get('/', [MailController::class, 'index'])->name('mail');

Route::resource('mails', MailController::class);
Route::resource('repositories', RepositoryController::class);
Route::resource('communications', CommunicationController::class);
Route::resource('accessions', AccessionController::class);
Route::resource('tools', ToolsController::class);
Route::resource('setting', SettingController::class);
Route::resource('localisations', LocalisationController::class);

