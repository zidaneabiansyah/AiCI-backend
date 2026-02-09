<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Mail\Payment\PaymentFailed;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckExpiredPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-expired {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired Xendit invoices and mark as failed';

    /**
     * Execute the console command.
     * 
     * Logic:
     * 1. Find all pending payments with expired_at < now()
     * 2. Mark as EXPIRED
     * 3. Send email notification
     * 4. Log for monitoring
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('[DRY RUN MODE] No changes will be made');
            $this->newLine();
        }

        $this->info('Checking for expired payments...');
        $this->newLine();

        // Find expired payments
        $expiredPayments = Payment::where('status', PaymentStatus::PENDING)
            ->where('expired_at', '<', now())
            ->with(['enrollment'])
            ->get();

        if ($expiredPayments->isEmpty()) {
            $this->info('[Expired Payments] No expired payments found');
            return Command::SUCCESS;
        }

        $this->info("[Expired Payments] Found {$expiredPayments->count()} expired payments");

        $processed = 0;
        $failed = 0;

        foreach ($expiredPayments as $payment) {
            $result = $this->processExpiredPayment($payment, $isDryRun);
            
            if ($result['success']) {
                $processed++;
            } else {
                $failed++;
            }
        }

        $this->newLine();
        
        if (!$isDryRun) {
            $this->info("Processed {$processed} expired payments");
            
            if ($failed > 0) {
                $this->error("Failed to process {$failed} payments");
            }
        } else {
            $this->info("Would process {$expiredPayments->count()} expired payments");
        }

        return Command::SUCCESS;
    }

    /**
     * Process a single expired payment
     * 
     * @param Payment $payment
     * @param bool $isDryRun
     * @return array
     */
    protected function processExpiredPayment(Payment $payment, bool $isDryRun): array
    {
        try {
            if (!$isDryRun) {
                $this->markPaymentAsExpired($payment);
                $this->sendExpirationEmail($payment);
                $this->line("   Processed: {$payment->invoice_number}");
            } else {
                $this->line("   Would process: {$payment->invoice_number}");
            }

            return ['success' => true];

        } catch (\Exception $e) {
            $this->error("   Failed to process {$payment->invoice_number}: {$e->getMessage()}");
            return ['success' => false];
        }
    }

    /**
     * Mark payment as expired
     * 
     * @param Payment $payment
     */
    protected function markPaymentAsExpired(Payment $payment): void
    {
        $payment->update([
            'status' => PaymentStatus::EXPIRED,
        ]);
    }

    /**
     * Send expiration email notification
     * 
     * @param Payment $payment
     */
    protected function sendExpirationEmail(Payment $payment): void
    {
        Mail::to($payment->enrollment->student_email)
            ->send(new PaymentFailed(
                $payment,
                'Invoice telah kadaluarsa. Silakan buat invoice baru untuk melanjutkan pembayaran.'
            ));
    }
}
