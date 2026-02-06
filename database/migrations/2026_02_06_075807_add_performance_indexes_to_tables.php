<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add performance indexes untuk optimize query speed
     * - Foreign keys untuk JOIN operations
     * - Status columns untuk filtering
     * - Search columns (email, phone, numbers)
     * - Timestamp columns untuk sorting
     */
    public function up(): void
    {
        // ENROLLMENTS TABLE
        Schema::table('enrollments', function (Blueprint $table) {
            // Status & search indexes
            $table->index('status', 'enrollments_status_idx');
            $table->index('enrollment_number', 'enrollments_enr_number_idx');
            $table->index('student_email', 'enrollments_student_email_idx');
            $table->index('student_phone', 'enrollments_student_phone_idx');
            
            // Timestamp indexes untuk sorting
            $table->index('enrolled_at', 'enrollments_enrolled_at_idx');
            $table->index('confirmed_at', 'enrollments_confirmed_at_idx');
            
            // Composite index untuk common queries
            $table->index(['status', 'enrolled_at'], 'enrollments_status_enrolled_idx');
        });

        // PAYMENTS TABLE
        Schema::table('payments', function (Blueprint $table) {
            // Status & search indexes
            $table->index('status', 'payments_status_idx');
            $table->index('invoice_number', 'payments_invoice_number_idx');
            $table->index('xendit_invoice_id', 'payments_xendit_invoice_idx');
            $table->index('payment_method', 'payments_payment_method_idx');
            
            // Timestamp indexes
            $table->index('paid_at', 'payments_paid_at_idx');
            $table->index('expired_at', 'payments_expired_at_idx');
            
            // Composite index untuk revenue queries
            $table->index(['status', 'paid_at'], 'payments_status_paid_idx');
        });

        // PROGRAMS TABLE
        Schema::table('programs', function (Blueprint $table) {
            // Status & filter indexes
            $table->index('is_active', 'programs_is_active_idx');
            $table->index('education_level', 'programs_education_level_idx');
            $table->index('slug', 'programs_slug_idx');
            
            // Sorting index
            $table->index('sort_order', 'programs_sort_order_idx');
            
            // Composite index
            $table->index(['is_active', 'sort_order'], 'programs_active_sort_idx');
        });

        // CLASSES TABLE
        Schema::table('classes', function (Blueprint $table) {
            // Status & filter indexes (skip yang sudah ada di migration)
            $table->index('slug', 'classes_slug_idx');
            
            // Composite index untuk common queries
            $table->index(['program_id', 'is_active'], 'classes_program_active_idx');
        });

        // CLASS_SCHEDULES TABLE
        Schema::table('class_schedules', function (Blueprint $table) {
            // Filter indexes (skip yang sudah ada)
            $table->index('day_of_week', 'class_schedules_day_idx');
        });

        // PLACEMENT_TESTS TABLE
        Schema::table('placement_tests', function (Blueprint $table) {
            // Status & filter indexes
            $table->index('is_active', 'placement_tests_is_active_idx');
            $table->index('education_level', 'placement_tests_edu_level_idx');
            $table->index('slug', 'placement_tests_slug_idx');
        });

        // TEST_ATTEMPTS TABLE
        Schema::table('test_attempts', function (Blueprint $table) {
            // Status & timestamp indexes
            $table->index('status', 'test_attempts_status_idx');
            $table->index('started_at', 'test_attempts_started_at_idx');
            $table->index('completed_at', 'test_attempts_completed_at_idx');
        });

        // ARTICLES TABLE
        Schema::table('articles', function (Blueprint $table) {
            // Status & filter indexes
            $table->index('status', 'articles_status_idx');
            $table->index('slug', 'articles_slug_idx');
            $table->index('published_at', 'articles_published_at_idx');
            
            // Composite index untuk published articles
            $table->index(['status', 'published_at'], 'articles_status_published_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ENROLLMENTS TABLE
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_status_idx');
            $table->dropIndex('enrollments_enr_number_idx');
            $table->dropIndex('enrollments_student_email_idx');
            $table->dropIndex('enrollments_student_phone_idx');
            $table->dropIndex('enrollments_enrolled_at_idx');
            $table->dropIndex('enrollments_confirmed_at_idx');
            $table->dropIndex('enrollments_status_enrolled_idx');
        });

        // PAYMENTS TABLE
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_idx');
            $table->dropIndex('payments_invoice_number_idx');
            $table->dropIndex('payments_xendit_invoice_idx');
            $table->dropIndex('payments_payment_method_idx');
            $table->dropIndex('payments_paid_at_idx');
            $table->dropIndex('payments_expired_at_idx');
            $table->dropIndex('payments_status_paid_idx');
        });

        // PROGRAMS TABLE
        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('programs_is_active_idx');
            $table->dropIndex('programs_education_level_idx');
            $table->dropIndex('programs_slug_idx');
            $table->dropIndex('programs_sort_order_idx');
            $table->dropIndex('programs_active_sort_idx');
        });

        // CLASSES TABLE
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('classes_slug_idx');
            $table->dropIndex('classes_program_active_idx');
        });

        // CLASS_SCHEDULES TABLE
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropIndex('class_schedules_day_idx');
        });

        // PLACEMENT_TESTS TABLE
        Schema::table('placement_tests', function (Blueprint $table) {
            $table->dropIndex('placement_tests_is_active_idx');
            $table->dropIndex('placement_tests_edu_level_idx');
            $table->dropIndex('placement_tests_slug_idx');
        });

        // TEST_ATTEMPTS TABLE
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->dropIndex('test_attempts_status_idx');
            $table->dropIndex('test_attempts_started_at_idx');
            $table->dropIndex('test_attempts_completed_at_idx');
        });

        // ARTICLES TABLE
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('articles_status_idx');
            $table->dropIndex('articles_slug_idx');
            $table->dropIndex('articles_published_at_idx');
            $table->dropIndex('articles_status_published_idx');
        });
    }
};
