<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field untuk data retention policy dan GDPR compliance:
     * - data_anonymized_at: Timestamp saat data di-anonymize
     * - scheduled_deletion_at: Timestamp untuk scheduled deletion
     * 
     * Data Retention Policy:
     * - Webhook logs: Auto-delete after 90 days
     * - Cancelled enrollments: Anonymize after 1 year
     * - Completed enrollments: Keep for 3 years, then anonymize
     * - Failed payments: Delete after 6 months
     */
    public function up(): void
    {
        // Add data retention fields to enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('data_anonymized_at')->nullable()->after('deleted_at');
            $table->timestamp('scheduled_deletion_at')->nullable()->after('data_anonymized_at');
            
            // Index untuk scheduled cleanup jobs
            $table->index('data_anonymized_at');
            $table->index('scheduled_deletion_at');
        });

        // Add data retention fields to payments
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('data_anonymized_at')->nullable()->after('deleted_at');
            $table->timestamp('scheduled_deletion_at')->nullable()->after('data_anonymized_at');
            
            // Index untuk scheduled cleanup jobs
            $table->index('data_anonymized_at');
            $table->index('scheduled_deletion_at');
        });

        // Add scheduled deletion to webhook_logs
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->timestamp('scheduled_deletion_at')->nullable()->after('created_at');
            
            // Index untuk scheduled cleanup jobs
            $table->index('scheduled_deletion_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['data_anonymized_at']);
            $table->dropIndex(['scheduled_deletion_at']);
            $table->dropColumn(['data_anonymized_at', 'scheduled_deletion_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['data_anonymized_at']);
            $table->dropIndex(['scheduled_deletion_at']);
            $table->dropColumn(['data_anonymized_at', 'scheduled_deletion_at']);
        });

        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['scheduled_deletion_at']);
            $table->dropColumn('scheduled_deletion_at');
        });
    }
};
