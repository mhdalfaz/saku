<?php

use App\Http\Controllers\LoanController;
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

Route::get('/choose', function () {
    return view('pages.choose');
})->name('choose');

Route::get('/settings', function () {
    return view('pages.settings.index');
})->name('settings');

// loans group
Route::prefix('loans')->name('loans.')->group(function () {
    Route::get('/create', [LoanController::class, 'createPage'])->name('create.page');
    Route::get('/', function() {
        return view('pages.loans.list');
    })->name('list.page');
    Route::get('/{loan}', function() {
        return view('pages.loans.detail');
    })->name('payment.page');
    Route::get('/{loan}/pay', [LoanController::class, 'paymentPage'])->name('payment.page');
});