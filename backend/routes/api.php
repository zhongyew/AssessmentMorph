<?php

use Illuminate\Http\Request;

// backend/routes/api.php
use App\Http\Controllers\GrammarController;
use Illuminate\Support\Facades\Route;

// 必须确保这行代码在 api.php 中，而不是在 web.php 中
Route::post('/check', [GrammarController::class, 'check']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/history', [GrammarController::class, 'store']);
Route::get('/history', [GrammarController::class, 'getHistory']);
Route::delete('/history/{id}', [GrammarController::class, 'deleteHistory']);