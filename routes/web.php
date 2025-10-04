<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/terms', fn () => view('terms'))->name('app.terms');

Route::get('/privacy', fn () => view('privacy'))->name('app.privacy');
