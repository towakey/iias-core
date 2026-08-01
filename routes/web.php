<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::get('/install', [InstallController::class, 'index'])->name('install');
Route::post('/install', [InstallController::class, 'setup']);

Route::get('/', function () {
    if (! file_exists(base_path('.env')) || empty(env('APP_KEY'))) {
        return redirect()->route('install');
    }

    return view('welcome');
})->name('home');
