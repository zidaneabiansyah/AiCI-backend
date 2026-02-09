<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\UserEnrollmentController;
use App\Http\Controllers\Api\UserPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ============================================
 * API ROUTES
 * ============================================
 * 
 * RESTful API untuk mobile app
 * Base URL: /api/v1
 * 
 * Authentication: Laravel Sanctum
 * Rate Limiting: 60 requests per minute
 */

Route::prefix('v1')->group(function () {
    
    /**
     * ============================================
     * PUBLIC ENDPOINTS (No Authentication)
     * ============================================
     */
    
    // Programs
    Route::get('/programs', [ProgramController::class, 'index'])
        ->name('api.programs.index');
    Route::get('/programs/{slug}', [ProgramController::class, 'show'])
        ->name('api.programs.show');
    
    // Classes
    Route::get('/classes', [ClassController::class, 'index'])
        ->name('api.classes.index');
    Route::get('/classes/{slug}', [ClassController::class, 'show'])
        ->name('api.classes.show');
    
    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])
        ->name('api.articles.index');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])
        ->name('api.articles.show');
    
    // Facilities
    Route::get('/facilities', [FacilityController::class, 'index'])
        ->name('api.facilities.index');
    Route::get('/facilities/{facility}', [FacilityController::class, 'show'])
        ->name('api.facilities.show');
    
    // Galleries
    Route::get('/galleries', [GalleryController::class, 'index'])
        ->name('api.galleries.index');
    Route::get('/galleries/{gallery}', [GalleryController::class, 'show'])
        ->name('api.galleries.show');
    
    /**
     * ============================================
     * AUTHENTICATED ENDPOINTS (Sanctum)
     * ============================================
     */
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // User Profile
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.user.profile');
        
        // User Enrollments
        Route::prefix('user/enrollments')->group(function () {
            Route::get('/', [UserEnrollmentController::class, 'index'])
                ->name('api.user.enrollments.index');
            Route::get('/{id}', [UserEnrollmentController::class, 'show'])
                ->name('api.user.enrollments.show');
        });
        
        // User Payments
        Route::prefix('user/payments')->group(function () {
            Route::get('/', [UserPaymentController::class, 'index'])
                ->name('api.user.payments.index');
            Route::get('/{id}', [UserPaymentController::class, 'show'])
                ->name('api.user.payments.show');
        });
        
    });
    
});

/**
 * ============================================
 * API HEALTH CHECK
 * ============================================
 */
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => 'v1',
    ]);
})->name('api.health');

/**
 * ============================================
 * FALLBACK ROUTE (404)
 * ============================================
 */
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
