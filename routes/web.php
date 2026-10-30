<?php

use App\Http\Controllers\CodeLoginController;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\UserController;

Route::view('/', 'home')->name('home');
Route::post('/langue', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::view('/connexion', 'login')->name('login');
    Route::post('/connexion', [CodeLoginController::class, 'send'])->middleware('throttle:5,1')->name('code.send');
    Route::view('/connexion/code', 'code')->name('code.form');
    Route::post('/connexion/code', [CodeLoginController::class, 'verify'])->middleware('throttle:10,1')->name('code.verify');
});

Route::middleware(['auth', RequireRole::class . ':locataire,admin'])->group(function () {
    Route::view('/espace', 'space')->name('dashboard');
});

Route::post('/deconnexion', [CodeLoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', RequireRole::class . ':admin'])
    ->prefix('admin')->group(function () {
        Route::view('/', 'admin')->name('admin');

        Route::get('/utilisateurs', [UserController::class, 'index'])
            ->name('admin.users');

        Route::post('/utilisateurs', [UserController::class, 'save'])
            ->name('admin.users.save');

        Route::get('/utilisateurs/{user}/modifier', [UserController::class, 'edit'])
            ->name('admin.users.edit');

        Route::patch('/utilisateurs/{user}', [UserController::class, 'update'])
            ->name('admin.users.update');

        Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');

        Route::view('/photos', 'admin.photos')
            ->name('admin.photos');

        Route::view('/fichiers', 'admin.files')
            ->name('admin.files');
    });
