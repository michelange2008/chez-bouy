<?php

use App\Http\Controllers\Admin\AnnuaireController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\StaySectionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CodeLoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\RentalCodeReminderCronController;
use App\Http\Controllers\StayController;
use App\Http\Middleware\RequireRole;
use App\Models\Rental;
use App\Models\StaySection;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/disponibilites', AvailabilityController::class)->name('availability');
Route::get('/photos/{photo}/image/{size?}', [HomeController::class, 'image'])->whereIn('size', ['thumbnail', 'preview'])->name('home.photos.image');
Route::view('/contact', 'contact', ['messageSent' => false])->name('contact');
Route::view('/contact/confirmation', 'contact', ['messageSent' => true])->name('contact.confirmation');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1,contact')->name('contact.store');
Route::view('/informations-legales', 'legal')->name('legal');
Route::post('/langue', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/webcron/rappels-codes', RentalCodeReminderCronController::class)
    ->withoutMiddleware('web')
    ->middleware('throttle:10,1')
    ->name('webcron.rental-code-reminders');

Route::middleware('guest')->group(function () {
    Route::view('/connexion', 'login')->name('login');
    Route::post('/connexion', [CodeLoginController::class, 'send'])->middleware('throttle:5,1')->name('code.send');
    Route::view('/connexion/code', 'code')->name('code.form');
    Route::post('/connexion/code', [CodeLoginController::class, 'verify'])->middleware('throttle:10,1')->name('code.verify');
});

Route::middleware(['auth', RequireRole::class.':locataire,admin'])->group(function () {
    Route::get('/espace', [StayController::class, 'index'])->name('dashboard');
    Route::get('/espace/photos/{photo}/image', [StayController::class, 'image'])->name('stay.photos.image');
    Route::get('/espace/guide', [StayController::class, 'downloadGuide'])->name('stay.guide');
    Route::get('/espace/arrivee', [StayController::class, 'show'])->name('stay.arrivee');
    Route::get('/espace/installation', [StayController::class, 'show'])->name('stay.installation');
    Route::get('/espace/appartement', [StayController::class, 'show'])->name('stay.appartement');
    Route::get('/espace/depart', [StayController::class, 'show'])->name('stay.depart');
    Route::get('/espace/chamonix', [StayController::class, 'show'])->name('stay.chamonix');
});

Route::post('/deconnexion', [CodeLoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', RequireRole::class.':admin'])
    ->prefix('admin')->group(function () {
        Route::view('/', 'admin')->name('admin');
        Route::get('/calendrier', [CalendarController::class, 'index'])->name('admin.calendar');
        Route::get('/calendrier/ajouter', [CalendarController::class, 'create'])->name('admin.calendar.create');
        Route::post('/calendrier', [CalendarController::class, 'store'])->name('admin.calendar.store');
        Route::get('/calendrier/{event}/modifier', [CalendarController::class, 'edit'])->whereNumber('event')->name('admin.calendar.edit');
        Route::patch('/calendrier/{event}', [CalendarController::class, 'update'])->whereNumber('event')->name('admin.calendar.update');
        Route::delete('/calendrier/{event}', [CalendarController::class, 'destroy'])->whereNumber('event')->name('admin.calendar.destroy');

        Route::get('/locations', [RentalController::class, 'index'])->name('admin.rentals');
        Route::get('/locations/ajouter', [RentalController::class, 'create'])->name('admin.rentals.create');
        Route::post('/locations', [RentalController::class, 'store'])->name('admin.rentals.store');
        Route::get('/locations/{rental}/modifier', [RentalController::class, 'edit'])->name('admin.rentals.edit');
        Route::patch('/locations/{rental}', [RentalController::class, 'update'])->name('admin.rentals.update');
        Route::patch('/locations/{rental}/remarque', [RentalController::class, 'updateRemarks'])->name('admin.rentals.remarks');
        Route::patch('/locations/{rental}/versements/{payment}', [RentalController::class, 'updatePayment'])
            ->whereIn('payment', ['deposit', 'balance'])->name('admin.rentals.payment');
        Route::patch('/locations/{rental}/suivi/{field}', [RentalController::class, 'updateChecklist'])
            ->whereIn('field', Rental::CHECKLIST)->name('admin.rentals.checklist');
        Route::post('/locations/{rental}/email-acces', [RentalController::class, 'sendAccessEmail'])->name('admin.rentals.send-access-email');
        Route::delete('/locations/{rental}', [RentalController::class, 'destroy'])->name('admin.rentals.destroy');

        Route::post('/pages-locataire/apercu', [StaySectionController::class, 'preview'])->name('admin.stay.preview');
        Route::get('/pages-locataire', [StaySectionController::class, 'index'])->name('admin.stay');
        Route::get('/pages-locataire/{page}', [StaySectionController::class, 'show'])->whereIn('page', StaySection::EDITABLE_PAGES)->name('admin.stay.show');
        Route::get('/pages-locataire/{page}/ajouter', [StaySectionController::class, 'create'])->whereIn('page', StaySection::EDITABLE_PAGES)->name('admin.stay.create');
        Route::post('/pages-locataire/{page}', [StaySectionController::class, 'store'])->whereIn('page', StaySection::EDITABLE_PAGES)->name('admin.stay.store');
        Route::get('/sections/{section}/modifier', [StaySectionController::class, 'edit'])->name('admin.stay.edit');
        Route::patch('/sections/{section}', [StaySectionController::class, 'update'])->name('admin.stay.update');
        Route::patch('/sections/{section}/publication', [StaySectionController::class, 'updatePublication'])->name('admin.stay.publication');
        Route::patch('/sections/{section}/ordre', [StaySectionController::class, 'move'])->name('admin.stay.move');
        Route::delete('/sections/{section}', [StaySectionController::class, 'destroy'])->name('admin.stay.destroy');

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

        Route::get('/fichiers', [FileController::class, 'index'])->name('admin.files');
        Route::get('/fichiers/ajouter', [FileController::class, 'create'])->name('admin.files.create');
        Route::post('/fichiers', [FileController::class, 'store'])->name('admin.files.store');
        Route::get('/fichiers/{file}/modifier', [FileController::class, 'edit'])->name('admin.files.edit');
        Route::patch('/fichiers/{file}', [FileController::class, 'update'])->name('admin.files.update');
        Route::delete('/fichiers/{file}', [FileController::class, 'destroy'])->name('admin.files.destroy');
        Route::get('/fichiers/{file}/telecharger', [FileController::class, 'download'])->name('admin.files.download');
        Route::get('/notes', [NoteController::class, 'index'])->name('admin.notes');
        Route::get('/notes/ajouter', [NoteController::class, 'create'])->name('admin.notes.create');
        Route::post('/notes', [NoteController::class, 'store'])->name('admin.notes.store');
        Route::get('/notes/{note}/modifier', [NoteController::class, 'edit'])->name('admin.notes.edit');
        Route::patch('/notes/{note}', [NoteController::class, 'update'])->name('admin.notes.update');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('admin.notes.destroy');
        Route::get('/annuaire', [AnnuaireController::class, 'index'])->name('admin.annuaire');
        Route::get('/annuaire/ajouter', [AnnuaireController::class, 'create'])->name('admin.annuaire.create');
        Route::post('/annuaire', [AnnuaireController::class, 'store'])->name('admin.annuaire.store');
        Route::get('/annuaire/{annuaire}/modifier', [AnnuaireController::class, 'edit'])->name('admin.annuaire.edit');
        Route::patch('/annuaire/{annuaire}', [AnnuaireController::class, 'update'])->name('admin.annuaire.update');
        Route::delete('/annuaire/{annuaire}', [AnnuaireController::class, 'destroy'])->name('admin.annuaire.destroy');
    });
