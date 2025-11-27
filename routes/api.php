<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\LoanController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/borrowers', [BorrowerController::class, 'getBorrowers']);
    Route::post('/borrowers', [BorrowerController::class, 'create']);

    // make a group for loan routes
    Route::prefix('loans')->group(function () {
        Route::get('/', [LoanController::class, 'getLoans']);
        Route::post('/', [LoanController::class, 'create']);
        Route::get('/{loan}', [LoanController::class, 'detail']);
        Route::post('/{loan}/pay', [LoanController::class, 'pay']);
    });
});