<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RegularItemController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShoppingItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('archives', ArchiveController::class);
    Route::get('shopping-items/stats', [ShoppingItemController::class, 'stats']);
    Route::apiResource('shopping-items', ShoppingItemController::class);
    Route::post('shopping-items/{id}/restore', [ShoppingItemController::class, 'restore']);

    Route::apiResource('regular-items', RegularItemController::class);
    Route::post('regular-items/{regularItem}/add-to-shopping', [RegularItemController::class, 'addToShopping']);

    Route::post('images', [ImageController::class, 'store']);

    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings', [SettingController::class, 'store']);
});
