<?php

use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\PhotoLocationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CodeLoginController;
use App\Http\Controllers\LocaleController;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::post('/langue', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::view('/connexion', 'login')->name('login');
    Route::post('/connexion', [CodeLoginController::class, 'send'])->middleware('throttle:5,1')->name('code.send');
    Route::view('/connexion/code', 'code')->name('code.form');
    Route::post('/connexion/code', [CodeLoginController::class, 'verify'])->middleware('throttle:10,1')->name('code.verify');
});

Route::middleware(['auth', RequireRole::class.':locataire,admin'])->group(function () {
    Route::view('/espace', 'space')->name('dashboard');
});

Route::post('/deconnexion', [CodeLoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', RequireRole::class.':admin'])
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

        Route::get('/photos', [PhotoController::class, 'index'])->name('admin.photos');
        Route::get('/photos/ajouter', [PhotoController::class, 'create'])->name('admin.photos.create');
        Route::post('/photos', [PhotoController::class, 'store'])->name('admin.photos.store');
        Route::get('/photos/{photo}/modifier', [PhotoController::class, 'edit'])->name('admin.photos.edit');
        Route::patch('/photos/{photo}', [PhotoController::class, 'update'])->name('admin.photos.update');
        Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('admin.photos.destroy');
        Route::get('/photos/{photo}/image', [PhotoController::class, 'image'])->name('admin.photos.image');

        Route::get('/emplacements', [PhotoLocationController::class, 'index'])->name('admin.locations');
        Route::post('/emplacements', [PhotoLocationController::class, 'store'])->name('admin.locations.store');
        Route::get('/emplacements/{location}/modifier', [PhotoLocationController::class, 'edit'])->name('admin.locations.edit');
        Route::patch('/emplacements/{location}', [PhotoLocationController::class, 'update'])->name('admin.locations.update');
        Route::delete('/emplacements/{location}', [PhotoLocationController::class, 'destroy'])->name('admin.locations.destroy');

        Route::view('/fichiers', 'admin.files')
            ->name('admin.files');
    });
