<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\WebhookLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:cleanup {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old data based on retention policy (GDPR compliance)';

    /**
     * Execute the console command.
     * 
     * Data Retention Policy:
     * 1. Webhook logs: Delete after 90 days
     * 2. Failed payments: Delete after 6 months
     * 3. Cancelled enrollments: Anonymize after 1 year
     * 4. Completed enrollments: Anonymize after 3 years
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('[DRY RUN MODE] No changes will be made');
            $this->newLine();
        }

        $this->info('Starting data cleanup process...');
        $this->newLine();

        // 1. Cleanup webhook logs (90 days)
        $this->cleanupWebhookLogs($isDryRun);

        // 2. Cleanup failed payments (6 months)
        $this->cleanupFailedPayments($isDryRun);

        // 3. Anonymize cancelled enrollments (1 year)
        $this->anonymizeCancelledEnrollments($isDryRun);

        // 4. Anonymize completed enrollments (3 years)
        $this->anonymizeCompletedEnrollments($isDryRun);

        $this->newLine();
        $this->info('Data cleanup completed successfully!');
        
        return Command::SUCCESS;
    }

    /**
     * Cleanup webhook logs older than 90 days
     */
    protected function cleanupWebhookLogs(bool $isDryRun): void
    {
        $cutoffDate = now()->subDays(90);
        
        $query = WebhookLog::where('created_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count > 0) {
            $this->info("[Webhook Logs] Found {$count} records older than 90 days");
            
            if (!$isDryRun) {
                $deleted = $query->delete();
                $this->line("   Deleted {$deleted} webhook logs");
            } else {
                $this->line("   Would delete {$count} webhook logs");
            }
        } else {
            $this->line('[Webhook Logs] No old records found');
        }
    }

    /**
     * Cleanup failed payments older than 6 months
     */
    protected function cleanupFailedPayments(bool $isDryRun): void
    {
        $cutoffDate = now()->subMonths(6);
        
        $query = Payment::where('status', 'failed')
            ->where('created_at', '<', $cutoffDate)
            ->whereNull('data_anonymized_at');
        
        $count = $query->count();

        if ($count > 0) {
            $this->info("[Failed Payments] Found {$count} records older than 6 months");
            
            if (!$isDryRun) {
                $deleted = $query->forceDelete();
                $this->line("   Deleted {$deleted} failed payments");
            } else {
                $this->line("   Would delete {$count} failed payments");
            }
        } else {
            $this->line('[Failed Payments] No old records found');
        }
    }

    /**
     * Anonymize cancelled enrollments older than 1 year
     */
    protected function anonymizeCancelledEnrollments(bool $isDryRun): void
    {
        $cutoffDate = now()->subYear();
        
        $query = Enrollment::where('status', 'cancelled')
            ->where('cancelled_at', '<', $cutoffDate)
            ->whereNull('data_anonymized_at');
        
        $count = $query->count();

        if ($count > 0) {
            $this->info("[Cancelled Enrollments] Found {$count} records older than 1 year");
            
            if (!$isDryRun) {
                $anonymized = 0;
                
                $query->chunk(100, function ($enrollments) use (&$anonymized) {
                    foreach ($enrollments as $enrollment) {
                        $this->anonymizeEnrollment($enrollment);
                        $anonymized++;
                    }
                });
                
                $this->line("   Anonymized {$anonymized} cancelled enrollments");
            } else {
                $this->line("   Would anonymize {$count} cancelled enrollments");
            }
        } else {
            $this->line('[Cancelled Enrollments] No old records found');
        }
    }

    /**
     * Anonymize completed enrollments older than 3 years
     */
    protected function anonymizeCompletedEnrollments(bool $isDryRun): void
    {
        $cutoffDate = now()->subYears(3);
        
        $query = Enrollment::where('status', 'completed')
            ->where('completed_at', '<', $cutoffDate)
            ->whereNull('data_anonymized_at');
        
        $count = $query->count();

        if ($count > 0) {
            $this->info("[Completed Enrollments] Found {$count} records older than 3 years");
            
            if (!$isDryRun) {
                $anonymized = 0;
                
                $query->chunk(100, function ($enrollments) use (&$anonymized) {
                    foreach ($enrollments as $enrollment) {
                        $this->anonymizeEnrollment($enrollment);
                        $anonymized++;
                    }
                });
                
                $this->line("   Anonymized {$anonymized} completed enrollments");
            } else {
                $this->line("   Would anonymize {$count} completed enrollments");
            }
        } else {
            $this->line('[Completed Enrollments] No old records found');
        }
    }

    /**
     * Anonymize enrollment data (GDPR compliance)
     * 
     * Replace PII with anonymized values while keeping statistical data
     */
    protected function anonymizeEnrollment(Enrollment $enrollment): void
    {
        $enrollment->update([
            'student_name' => 'ANONYMIZED',
            'student_email' => 'anonymized_' . $enrollment->id . '@deleted.local',
            'student_phone' => '000000000000',
            'parent_name' => 'ANONYMIZED',
            'parent_email' => 'anonymized_parent_' . $enrollment->id . '@deleted.local',
            'parent_phone' => '000000000000',
            'special_requirements' => null,
            'notes' => 'Data anonymized per retention policy',
            'data_anonymized_at' => now(),
        ]);

        // Also anonymize related payment if exists
        if ($enrollment->payment) {
            $enrollment->payment->update([
                'account_number' => null,
                'xendit_response' => null,
                'payment_proof' => null,
                'data_anonymized_at' => now(),
            ]);
        }
    }
}
