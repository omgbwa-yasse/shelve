<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/setting', function () {
    return view('setting');
})->name('home');


use App\Http\Controllers\MailController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\AccessionController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocalisationController;

Route::get('/', [MailController::class, 'index'])->name('mail');

Route::get('mail', [MailController::class, 'index'])->name('mail');
Route::get('repository', [RepositoryController::class, 'index'])->name('repository');
Route::get('communication', [CommunicationController::class, 'index'])->name('communication');
Route::get('accession', [AccessionController::class, 'index'])->name('accession');
Route::get('tools', [ToolsController::class, 'index'])->name('tools');
Route::get('setting', [SettingController::class, 'index'])->name('setting');
Route::get('localisation', [LocalisationController::class, 'index'])->name('localisation');

