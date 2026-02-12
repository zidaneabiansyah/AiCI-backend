<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'AICI-UMG API Backend',
        'status' => 'online'
    ]);
});

// Payment Routes - Redirection from Xendit
Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/success/{payment}', [App\Http\Controllers\PaymentController::class, 'success'])->name('success');
    Route::get('/failed/{payment}', [App\Http\Controllers\PaymentController::class, 'failed'])->name('failed');
});

require __DIR__.'/auth.php';
