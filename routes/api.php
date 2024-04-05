<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('transactions')->group(function () {
    Route::post('start', [TransactionController::class, 'start']);
    Route::post('commit/{transactionId}', [TransactionController::class, 'commit']);
    Route::post('rollback/{transactionId}', [TransactionController::class, 'rollback']);
});
