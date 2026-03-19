<?php

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('under-construction');
})->name('home');

Route::get('/welcome', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return redirect()->route('admin.about');
        }

        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/nosotros');

    Route::livewire('productos', 'pages::admin.products')->name('admin.products');
    Route::livewire('galeria', 'pages::admin.gallery')->name('admin.gallery');
    Route::livewire('nosotros', 'pages::admin.about')->name('admin.about');
    Route::livewire('sitio', 'pages::admin.site')->name('admin.site');
});

require __DIR__.'/settings.php';
