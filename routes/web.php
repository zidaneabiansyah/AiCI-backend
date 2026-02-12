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

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

// Placement Test Routes
Route::prefix('placement-test')->name('placement-test.')->group(function () {
    // Public routes
    Route::get('/', [App\Http\Controllers\PlacementTestController::class, 'index'])->name('index');
    Route::get('/{test:slug}', [App\Http\Controllers\PlacementTestController::class, 'show'])->name('show');
    
    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::post('/{test}/start', [App\Http\Controllers\PlacementTestController::class, 'start'])->name('start');
        Route::get('/attempt/{attempt}', [App\Http\Controllers\PlacementTestController::class, 'attempt'])->name('attempt');
        Route::post('/attempt/{attempt}/answer', [App\Http\Controllers\PlacementTestController::class, 'submitAnswer'])->name('answer');
        Route::post('/attempt/{attempt}/complete', [App\Http\Controllers\PlacementTestController::class, 'complete'])->name('complete');
        Route::get('/result/{attempt}', [App\Http\Controllers\PlacementTestController::class, 'result'])->name('result');
        Route::get('/result/{attempt}/download', [App\Http\Controllers\PlacementTestController::class, 'downloadResult'])->name('download-result');
    });
});

// Class Routes
Route::prefix('classes')->name('classes.')->group(function () {
    Route::get('/', [App\Http\Controllers\ClassController::class, 'index'])->name('index');
    Route::get('/{class:slug}', [App\Http\Controllers\ClassController::class, 'show'])->name('show');
});

// Enrollment Routes
Route::middleware('auth')->prefix('enrollments')->name('enrollments.')->group(function () {
    Route::get('/', [App\Http\Controllers\EnrollmentController::class, 'index'])->name('index');
    Route::get('/create/{class}', [App\Http\Controllers\EnrollmentController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\EnrollmentController::class, 'store'])->name('store');
    Route::get('/{enrollment}', [App\Http\Controllers\EnrollmentController::class, 'show'])->name('show');
    Route::post('/{enrollment}/cancel', [App\Http\Controllers\EnrollmentController::class, 'cancel'])->name('cancel');
});

// Payment Routes
Route::middleware('auth')->prefix('payments')->name('payments.')->group(function () {
    Route::post('/create/{enrollment}', [App\Http\Controllers\PaymentController::class, 'create'])->name('create');
    Route::get('/{payment}', [App\Http\Controllers\PaymentController::class, 'show'])->name('show');
    Route::get('/{payment}/check', [App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('check');
    Route::get('/{payment}/receipt', [App\Http\Controllers\PaymentController::class, 'receipt'])->name('receipt');
    
    // Xendit redirect URLs (public access via signed URL)
    Route::withoutMiddleware('auth')->group(function () {
        Route::get('/success/{payment}', [App\Http\Controllers\PaymentController::class, 'success'])->name('success');
        Route::get('/failed/{payment}', [App\Http\Controllers\PaymentController::class, 'failed'])->name('failed');
    });
});

// Webhook Routes (public, no auth)
// Rate limiting: 100 requests per minute to prevent abuse
Route::post('/webhooks/xendit', [App\Http\Controllers\WebhookController::class, 'xendit'])
    ->name('webhooks.xendit')
    ->middleware('throttle:100,1'); // 100 requests per 1 minute

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::resource('programs', App\Http\Controllers\Admin\ProgramController::class);
    Route::resource('classes', App\Http\Controllers\Admin\ClassController::class);
    Route::resource('questions', App\Http\Controllers\Admin\QuestionController::class);
    Route::resource('enrollments', App\Http\Controllers\Admin\EnrollmentController::class);
    Route::post('enrollments/{enrollment}/status', [App\Http\Controllers\Admin\EnrollmentController::class, 'updateStatus'])->name('enrollments.update-status');

    Route::prefix('content')->name('content.')->group(function () {
        Route::get('articles', [App\Http\Controllers\Admin\ContentController::class, 'articles'])->name('articles.index');
        Route::get('gallery', [App\Http\Controllers\Admin\ContentController::class, 'gallery'])->name('gallery.index');
        Route::get('facilities', [App\Http\Controllers\Admin\ContentController::class, 'facilities'])->name('facilities.index');
    });
});

require __DIR__.'/auth.php';
