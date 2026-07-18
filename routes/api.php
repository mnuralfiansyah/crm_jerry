<?php

use App\Http\Controllers\CrmController;
use Illuminate\Support\Facades\Route;

Route::get('/cabang', [CrmController::class, 'cabang']);
Route::get('/branches', [CrmController::class, 'cabang']);





Route::get('/sales', [CrmController::class, 'sales']);
Route::get('/salespeople', [CrmController::class, 'sales']);


Route::get('/receivables', [CrmController::class, 'terima_piutang']);
Route::get('/terima_piutang_list', [CrmController::class, 'terima_piutang']);



Route::get('/pendapatan', [CrmController::class, 'pendapatan']);
Route::get('/penjualan', [CrmController::class, 'penjualan']);
Route::get('/retur', [CrmController::class, 'retur']);
Route::get('/indent', [CrmController::class, 'indent']);
