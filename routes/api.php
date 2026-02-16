<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ClassController as ApiClassController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\UserEnrollmentController;
use App\Http\Controllers\Api\UserPaymentController;
use App\Http\Controllers\PlacementTestController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ============================================
 * API ROUTES
 * ============================================
 * 
 * RESTful API untuk AICI Platform
 * Base URL: /api/v1
 */

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    
    /**
     * ============================================
     * PUBLIC ENDPOINTS (No Authentication)
     * ============================================
     */
    
    // Programs
    Route::get('/programs', [ProgramController::class, 'index'])->name('api.programs.index');
    Route::get('/programs/{slug}', [ProgramController::class, 'show'])->name('api.programs.show');
    
    // Classes
    Route::get('/classes', [ClassController::class, 'index'])->name('api.classes.index');
    Route::get('/classes/{slug}', [ClassController::class, 'show'])->name('api.classes.show');
    
    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('api.articles.index');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('api.articles.show');
    
    // Facilities
    Route::get('/facilities', [FacilityController::class, 'index'])->name('api.facilities.index');
    Route::get('/facilities/{facility}', [FacilityController::class, 'show'])->name('api.facilities.show');
    
    // Galleries
    Route::get('/galleries', [GalleryController::class, 'index'])->name('api.galleries.index');
    Route::get('/galleries/{gallery}', [GalleryController::class, 'show'])->name('api.galleries.show');

    // Placement Tests
    Route::get('/placement-tests', [PlacementTestController::class, 'index'])->name('api.placement-tests.index');
    Route::get('/placement-tests/{test:slug}', [PlacementTestController::class, 'show'])->name('api.placement-tests.show');
    
    // Content Management (Public Read-Only)
    Route::get('/content/testimonials', [ContentController::class, 'testimonials'])->name('api.content.testimonials');
    Route::get('/content/partners', [ContentController::class, 'partners'])->name('api.content.partners');
    Route::get('/content/settings', [ContentController::class, 'settings'])->name('api.content.settings');
    Route::get('/content/team', [ContentController::class, 'team'])->name('api.content.team');
    Route::get('/content/pages', [ContentController::class, 'pageContent'])->name('api.content.pages');
    Route::post('/content/contact', [ContentController::class, 'sendContact'])->name('api.content.contact');
    
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
        
        // Placement Test Attempt
        Route::prefix('placement-tests')->group(function () {
            Route::post('/{test}/start', [PlacementTestController::class, 'start'])->name('api.placement-tests.start');
            Route::get('/attempt/{attempt}', [PlacementTestController::class, 'attempt'])->name('api.placement-tests.attempt');
            Route::post('/attempt/{attempt}/answer', [PlacementTestController::class, 'submitAnswer'])->name('api.placement-tests.answer');
            Route::post('/attempt/{attempt}/complete', [PlacementTestController::class, 'complete'])->name('api.placement-tests.complete');
            Route::get('/result/{attempt}', [PlacementTestController::class, 'result'])->name('api.placement-tests.result');
            Route::get('/result/{attempt}/download', [PlacementTestController::class, 'downloadResult'])->name('api.placement-tests.download');
        });

        // Enrollments
        Route::prefix('enrollments')->group(function () {
            Route::get('/', [EnrollmentController::class, 'index'])->name('api.enrollments.index');
            Route::get('/create/{class}', [EnrollmentController::class, 'create'])->name('api.enrollments.create');
            Route::post('/', [EnrollmentController::class, 'store'])->name('api.enrollments.store');
            Route::get('/{enrollment}', [EnrollmentController::class, 'show'])->name('api.enrollments.show');
            Route::post('/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('api.enrollments.cancel');
        });
        
        // Payments
        Route::prefix('payments')->group(function () {
            Route::post('/create/{enrollment}', [PaymentController::class, 'create'])->name('api.payments.create');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('api.payments.show');
            Route::get('/{payment}/check', [PaymentController::class, 'checkStatus'])->name('api.payments.check');
            Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('api.payments.receipt');
        });
        
    });

    /**
     * ============================================
     * WEBHOOKS
     * ============================================
     */
    Route::post('/webhooks/xendit', [WebhookController::class, 'xendit'])
        ->name('api.webhooks.xendit')
        ->middleware('throttle:100,1');
    
});

/**
 * ============================================
 * ADMIN ENDPOINTS
 * ============================================
 */
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'overview'])->name('admin.analytics.overview');
        Route::get('/revenue', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'revenue'])->name('admin.analytics.revenue');
        Route::get('/enrollments', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'enrollments'])->name('admin.analytics.enrollments');
        Route::get('/students', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'students'])->name('admin.analytics.students');
        Route::get('/tests', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'tests'])->name('admin.analytics.tests');
    });
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\Api\Admin\ReportsController::class, 'summary'])->name('admin.reports.summary');
        Route::get('/export/revenue', [\App\Http\Controllers\Api\Admin\ReportsController::class, 'exportRevenue'])->name('admin.reports.export.revenue');
        Route::get('/export/enrollment', [\App\Http\Controllers\Api\Admin\ReportsController::class, 'exportEnrollment'])->name('admin.reports.export.enrollment');
        Route::get('/export/student', [\App\Http\Controllers\Api\Admin\ReportsController::class, 'exportStudent'])->name('admin.reports.export.student');
    });
    
    // Schedules
    Route::prefix('schedules')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\ScheduleController::class, 'index'])->name('admin.schedules.index');
        Route::get('/{id}', [\App\Http\Controllers\Api\Admin\ScheduleController::class, 'show'])->name('admin.schedules.show');
        Route::post('/', [\App\Http\Controllers\Api\Admin\ScheduleController::class, 'store'])->name('admin.schedules.store');
        Route::patch('/{id}', [\App\Http\Controllers\Api\Admin\ScheduleController::class, 'update'])->name('admin.schedules.update');
        Route::delete('/{id}', [\App\Http\Controllers\Api\Admin\ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
    });
    
    // Content Management (Admin CRUD)
    Route::prefix('content')->group(function () {
        // Testimonials
        Route::post('/testimonials', [ContentController::class, 'storeTestimonial'])->name('admin.content.testimonials.store');
        Route::patch('/testimonials/{id}', [ContentController::class, 'updateTestimonial'])->name('admin.content.testimonials.update');
        Route::delete('/testimonials/{id}', [ContentController::class, 'deleteTestimonial'])->name('admin.content.testimonials.delete');
        Route::post('/testimonials/reorder', [ContentController::class, 'reorderTestimonials'])->name('admin.content.testimonials.reorder');
        
        // Partners
        Route::post('/partners', [ContentController::class, 'storePartner'])->name('admin.content.partners.store');
        Route::patch('/partners/{id}', [ContentController::class, 'updatePartner'])->name('admin.content.partners.update');
        Route::delete('/partners/{id}', [ContentController::class, 'deletePartner'])->name('admin.content.partners.delete');
        Route::post('/partners/reorder', [ContentController::class, 'reorderPartners'])->name('admin.content.partners.reorder');
        
        // Settings
        Route::patch('/settings', [ContentController::class, 'updateSettings'])->name('admin.content.settings.update');
        
        // Team
        Route::post('/team', [ContentController::class, 'storeTeamMember'])->name('admin.content.team.store');
        Route::patch('/team/{id}', [ContentController::class, 'updateTeamMember'])->name('admin.content.team.update');
        Route::delete('/team/{id}', [ContentController::class, 'deleteTeamMember'])->name('admin.content.team.delete');
        Route::post('/team/reorder', [ContentController::class, 'reorderTeamMembers'])->name('admin.content.team.reorder');
        
        // Page Content
        Route::post('/pages', [ContentController::class, 'storePageContent'])->name('admin.content.pages.store');
        Route::patch('/pages/{key}', [ContentController::class, 'updatePageContent'])->name('admin.content.pages.update');
        
        // Contact Messages
        Route::get('/contact-messages', [ContentController::class, 'contactMessages'])->name('admin.content.contact.index');
        Route::patch('/contact-messages/{id}/read', [ContentController::class, 'markContactAsRead'])->name('admin.content.contact.read');
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
