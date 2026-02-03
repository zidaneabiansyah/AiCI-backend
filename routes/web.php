<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/program', function () {
    return Inertia::render('Program');
})->name('program');

Route::get('/facility', function () {
    return Inertia::render('Facility');
})->name('facility');

Route::get('/gallery', function () {
    return Inertia::render('Gallery');
})->name('gallery');

Route::get('/research', function () {
    return Inertia::render('Research');
})->name('research');

Route::get('/articles', function () {
    return Inertia::render('Article');
})->name('articles');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::get('/join', function () {
    return Inertia::render('JoinNow');
})->name('join');

require __DIR__.'/auth.php';
