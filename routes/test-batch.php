<?php

Route::get('/test-batch', function () {
    return view('test-batch');
})->middleware('auth');
