<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes to improve query performance
     */
    public function up(): void
    {
        // Programs table indexes
        Schema::table('programs', function (Blueprint $table) {
            $table->index('slug', 'idx_programs_slug');
            $table->index('is_active', 'idx_programs_is_active');
            $table->index(['education_level', 'is_active'], 'idx_programs_education_active');
            $table->index('sort_order', 'idx_programs_sort_order');
        });

        // Classes table indexes
        Schema::table('classes', function (Blueprint $table) {
            $table->index('slug', 'idx_classes_slug');
            $table->index('program_id', 'idx_classes_program_id');
            $table->index('is_active', 'idx_classes_is_active');
            $table->index(['program_id', 'is_active'], 'idx_classes_program_active');
            $table->index('level', 'idx_classes_level');
        });

        // Articles table indexes
        Schema::table('articles', function (Blueprint $table) {
            $table->index('slug', 'idx_articles_slug');
            $table->index('status', 'idx_articles_status');
            $table->index('published_at', 'idx_articles_published_at');
            $table->index(['status', 'published_at'], 'idx_articles_status_published');
            $table->index('category', 'idx_articles_category');
            $table->index('created_by', 'idx_articles_created_by');
        });

        // Placement Tests table indexes
        Schema::table('placement_tests', function (Blueprint $table) {
            $table->index('slug', 'idx_placement_tests_slug');
            $table->index('education_level', 'idx_placement_tests_education');
            $table->index('is_active', 'idx_placement_tests_is_active');
            $table->index(['education_level', 'is_active'], 'idx_placement_tests_education_active');
        });

        // Facilities table indexes
        Schema::table('facilities', function (Blueprint $table) {
            $table->index('slug', 'idx_facilities_slug');
            $table->index('type', 'idx_facilities_type');
            $table->index('is_active', 'idx_facilities_is_active');
            $table->index('sort_order', 'idx_facilities_sort_order');
        });

        // Galleries table indexes
        Schema::table('galleries', function (Blueprint $table) {
            $table->index('category', 'idx_galleries_category');
            $table->index('is_featured', 'idx_galleries_is_featured');
            $table->index('event_date', 'idx_galleries_event_date');
            $table->index('sort_order', 'idx_galleries_sort_order');
        });

        // Testimonials table indexes
        Schema::table('testimonials', function (Blueprint $table) {
            $table->index('order', 'idx_testimonials_order');
        });

        // Partners table indexes
        Schema::table('partners', function (Blueprint $table) {
            $table->index('order', 'idx_partners_order');
        });

        // Team Members table indexes
        Schema::table('team_members', function (Blueprint $table) {
            $table->index('role_type', 'idx_team_members_role_type');
            $table->index('order', 'idx_team_members_order');
        });

        // Enrollments table indexes
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('user_id', 'idx_enrollments_user_id');
            $table->index('class_id', 'idx_enrollments_class_id');
            $table->index('status', 'idx_enrollments_status');
            $table->index(['user_id', 'status'], 'idx_enrollments_user_status');
            $table->index('created_at', 'idx_enrollments_created_at');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('enrollment_id', 'idx_payments_enrollment_id');
            $table->index('status', 'idx_payments_status');
            $table->index('payment_method', 'idx_payments_payment_method');
            $table->index(['status', 'created_at'], 'idx_payments_status_created');
            $table->index('xendit_invoice_id', 'idx_payments_xendit_invoice_id');
        });

        // Test Attempts table indexes
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->index('user_id', 'idx_test_attempts_user_id');
            $table->index('placement_test_id', 'idx_test_attempts_placement_test_id');
            $table->index('status', 'idx_test_attempts_status');
            $table->index(['user_id', 'placement_test_id'], 'idx_test_attempts_user_test');
            $table->index('created_at', 'idx_test_attempts_created_at');
        });

        // Class Schedules table indexes
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->index('class_id', 'idx_class_schedules_class_id');
            $table->index('is_available', 'idx_class_schedules_is_available');
            $table->index('start_date', 'idx_class_schedules_start_date');
            $table->index(['class_id', 'is_available'], 'idx_class_schedules_class_available');
        });

        // Users table indexes (if not already exists)
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
            $table->index('role', 'idx_users_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Programs
        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('idx_programs_slug');
            $table->dropIndex('idx_programs_is_active');
            $table->dropIndex('idx_programs_education_active');
            $table->dropIndex('idx_programs_sort_order');
        });

        // Classes
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('idx_classes_slug');
            $table->dropIndex('idx_classes_program_id');
            $table->dropIndex('idx_classes_is_active');
            $table->dropIndex('idx_classes_program_active');
            $table->dropIndex('idx_classes_level');
        });

        // Articles
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_slug');
            $table->dropIndex('idx_articles_status');
            $table->dropIndex('idx_articles_published_at');
            $table->dropIndex('idx_articles_status_published');
            $table->dropIndex('idx_articles_category');
            $table->dropIndex('idx_articles_created_by');
        });

        // Placement Tests
        Schema::table('placement_tests', function (Blueprint $table) {
            $table->dropIndex('idx_placement_tests_slug');
            $table->dropIndex('idx_placement_tests_education');
            $table->dropIndex('idx_placement_tests_is_active');
            $table->dropIndex('idx_placement_tests_education_active');
        });

        // Facilities
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropIndex('idx_facilities_slug');
            $table->dropIndex('idx_facilities_type');
            $table->dropIndex('idx_facilities_is_active');
            $table->dropIndex('idx_facilities_sort_order');
        });

        // Galleries
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex('idx_galleries_category');
            $table->dropIndex('idx_galleries_is_featured');
            $table->dropIndex('idx_galleries_event_date');
            $table->dropIndex('idx_galleries_sort_order');
        });

        // Testimonials
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex('idx_testimonials_order');
        });

        // Partners
        Schema::table('partners', function (Blueprint $table) {
            $table->dropIndex('idx_partners_order');
        });

        // Team Members
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropIndex('idx_team_members_role_type');
            $table->dropIndex('idx_team_members_order');
        });

        // Enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_user_id');
            $table->dropIndex('idx_enrollments_class_id');
            $table->dropIndex('idx_enrollments_status');
            $table->dropIndex('idx_enrollments_user_status');
            $table->dropIndex('idx_enrollments_created_at');
        });

        // Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_enrollment_id');
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_payment_method');
            $table->dropIndex('idx_payments_status_created');
            $table->dropIndex('idx_payments_xendit_invoice_id');
        });

        // Test Attempts
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_test_attempts_user_id');
            $table->dropIndex('idx_test_attempts_placement_test_id');
            $table->dropIndex('idx_test_attempts_status');
            $table->dropIndex('idx_test_attempts_user_test');
            $table->dropIndex('idx_test_attempts_created_at');
        });

        // Class Schedules
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_class_schedules_class_id');
            $table->dropIndex('idx_class_schedules_is_available');
            $table->dropIndex('idx_class_schedules_start_date');
            $table->dropIndex('idx_class_schedules_class_available');
        });

        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });
    }
};
