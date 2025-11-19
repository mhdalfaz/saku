<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/home', function () {
    return view('pages.home');
})->name('home');

Route::get('/settings', function () {
    return view('pages.settings.index');
})->name('settings');