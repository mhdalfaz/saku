<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\CustodianController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\LoanTransactionController;
use App\Http\Controllers\Api\SavingController;
use App\Http\Controllers\Api\SavingTransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/borrowers', [BorrowerController::class, 'getBorrowers']);
    Route::post('/borrowers', [BorrowerController::class, 'create']);

    Route::get('/custodians', [CustodianController::class, 'getCustodians']);
    Route::post('/custodians', [CustodianController::class, 'create']);

    Route::prefix('savings')->group(function () {
        Route::get('/', [SavingController::class, 'getSavings']);
        Route::post('/', [SavingController::class, 'create']);
        Route::get('/custodian/{custodian}', [SavingController::class, 'getSavingsByCustodian']);
        Route::get('/{saving}', [SavingController::class, 'detail']);
        Route::post('/{saving}/deposit', [SavingController::class, 'deposit']);
        Route::post('/{saving}/withdraw', [SavingController::class, 'withdraw']);
        Route::post('/{saving}/profit', [SavingController::class, 'profit']);
        Route::post('/{saving}/close', [SavingController::class, 'close']);
        Route::delete('/{saving}', [SavingController::class, 'delete']);
    });

    Route::prefix('saving-transactions')->group(function () {
        Route::delete('/{savingTransaction}', [SavingTransactionController::class, 'delete']);
    });

    Route::prefix('loans')->group(function () {
        Route::get('/', [LoanController::class, 'getLoans']);
        Route::post('/', [LoanController::class, 'create']);
        Route::get('/{loan}', [LoanController::class, 'detail']);
        Route::post('/{loan}/pay', [LoanController::class, 'pay']);
        Route::delete('/{loan}', [LoanController::class, 'delete']);
        Route::get('/borrower/{borrower}', [LoanController::class, 'getLoansByBorrower']);
    });

    Route::prefix('loan-transactions')->group(function () {
        Route::delete('/{loanTransaction}', [LoanTransactionController::class, 'delete']);
    });
});
