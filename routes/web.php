<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/terms', function () {
    return view('terms');
})->name('app.terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('app.privacy');
