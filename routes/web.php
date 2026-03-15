<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/productos');

    Route::livewire('productos', 'pages::admin.products')->name('admin.products');
    Route::livewire('galeria', 'pages::admin.gallery')->name('admin.gallery');
    Route::livewire('nosotros', 'pages::admin.about')->name('admin.about');
    Route::livewire('sitio', 'pages::admin.site')->name('admin.site');
});

require __DIR__.'/settings.php';
