<?php

use App\Http\Controllers\Api\V1\AppBrandingController;
use App\Http\Controllers\Api\V1\AppBroadcastController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FinancialTargetController;
use App\Http\Controllers\Api\V1\ReceiptController;
use App\Http\Controllers\Api\V1\ShoppingListController;
use App\Http\Controllers\Api\V1\ScanReceiptController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/app/branding', [AppBrandingController::class, 'show']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [UserController::class, 'show']);
        Route::put('/user', [UserController::class, 'update']);

        Route::get('/app/broadcasts', [AppBroadcastController::class, 'index']);
        Route::get('/app/broadcasts/latest', [AppBroadcastController::class, 'latest']);
        Route::get('/wallets', [WalletController::class, 'show']);
        Route::put('/wallets', [WalletController::class, 'update']);

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::get('/transactions/summary/monthly', [TransactionController::class, 'monthlySummary']);
        Route::get('/transactions/summary/by-category', [TransactionController::class, 'byCategorySummary']);
        Route::post('/transactions/scan-receipt', ScanReceiptController::class);
        Route::apiResource('transactions', TransactionController::class)
            ->except(['create', 'edit'])
            ->parameters(['transactions' => 'id']);

        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::get('/budgets/current', [BudgetController::class, 'current']);

        Route::post('/receipts/upload', [ReceiptController::class, 'upload']);

        Route::get('/financial-targets', [FinancialTargetController::class, 'index']);
        Route::post('/financial-targets', [FinancialTargetController::class, 'store']);
        Route::put('/financial-targets/{id}', [FinancialTargetController::class, 'update']);
        Route::delete('/financial-targets/{id}', [FinancialTargetController::class, 'destroy']);

        Route::get('/shopping-list', [ShoppingListController::class, 'index']);
        Route::post('/shopping-list', [ShoppingListController::class, 'store']);
        Route::put('/shopping-list/{id}', [ShoppingListController::class, 'update']);
        Route::delete('/shopping-list/{id}', [ShoppingListController::class, 'destroy']);
    });
});
