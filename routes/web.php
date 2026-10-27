<?php

use App\Http\Controllers\CodeLoginController;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::view('/connexion', 'login')->name('login');
    Route::post('/connexion', [CodeLoginController::class, 'send'])->middleware('throttle:5,1')->name('code.send');
    Route::view('/connexion/code', 'code')->name('code.form');
    Route::post('/connexion/code', [CodeLoginController::class, 'verify'])->middleware('throttle:10,1')->name('code.verify');
});

Route::middleware(['auth', RequireRole::class.':locataire,admin'])->group(function () {
    Route::view('/espace', 'space')->name('dashboard');
    Route::post('/deconnexion', [CodeLoginController::class, 'logout'])->name('logout');
});

Route::view('/admin', 'admin')
    ->middleware(['auth', RequireRole::class.':admin'])->name('admin');
