<?php

use App\Http\Controllers\CrmController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', [CrmController::class, 'health']);

Route::prefix('v1/dashboard')->group(function () {
    Route::get('/branches', [CrmController::class, 'dashboard_branches']);
    Route::get('/salespeople', [CrmController::class, 'dashboard_salespeople']);

    Route::get('/summary', [CrmController::class, 'dashboard_summary']);
    Route::get('/trend', [CrmController::class, 'dashboard_trend']);
    Route::get('/sales-performance', [CrmController::class, 'dashboard_sales_performance']);

    Route::get('/customer-summary', [CrmController::class, 'customer_summary']);
    Route::get('/customers', [CrmController::class, 'customers']);
    Route::get('/customers/inactive', [CrmController::class, 'inactive_customers']);
    Route::get('/customers/{id}/sales-history', [CrmController::class, 'customer_sales_history']);
    Route::get('/customers/{id}/transactions', [CrmController::class, 'customer_transactions']);

    Route::get('/stock/summary', [CrmController::class, 'stock_summary']);
    Route::get('/stock', [CrmController::class, 'stok']);
    Route::get('/stock/movements', [CrmController::class, 'stock_movements']);

    Route::get('/receivables/summary', [CrmController::class, 'receivables_summary']);
    Route::get('/receivables', [CrmController::class, 'receivables']);
    Route::get('/receivables/aging', [CrmController::class, 'receivables_aging']);

    Route::get('/payables/summary', [CrmController::class, 'payables_summary']);
    Route::get('/payables', [CrmController::class, 'payables']);
    Route::get('/payables/aging', [CrmController::class, 'payables_aging']);
});

Route::get('/cabang', [CrmController::class, 'cabang']);
Route::get('/branches', [CrmController::class, 'cabang']);





Route::get('/sales', [CrmController::class, 'sales']);
Route::get('/salespeople', [CrmController::class, 'sales']);
Route::get('/stok', [CrmController::class, 'stok']);
Route::get('/pelanggan-periode', [CrmController::class, 'pelanggan_periode']);
Route::get('/hutang-toko', [CrmController::class, 'hutang_toko']);
Route::get('/piutang-pelanggan', [CrmController::class, 'piutang_pelanggan']);


Route::get('/receivables', [CrmController::class, 'terima_piutang']);
Route::get('/terima_piutang_list', [CrmController::class, 'terima_piutang']);



Route::get('/pendapatan', [CrmController::class, 'pendapatan']);
Route::get('/penjualan', [CrmController::class, 'penjualan']);
Route::get('/retur', [CrmController::class, 'retur']);
Route::get('/indent', [CrmController::class, 'indent']);
