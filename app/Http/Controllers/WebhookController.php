<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk Xendit Webhooks
 * 
 * Endpoint:
 * - POST /webhooks/xendit - Handle Xendit payment callbacks
 * 
 * Security Features:
 * - Verify webhook signature (HMAC SHA256)
 * - Log all webhook attempts to database
 * - Detect replay attacks
 * - Rate limiting (configured in routes)
 * - IP whitelist check (optional)
 * 
 * Xendit Webhook Documentation:
 * https://developers.xendit.co/api-reference/#webhooks
 */
class WebhookController extends Controller
{
    /**
     * Constructor - inject service
     */
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle Xendit webhook
     * 
     * Xendit akan POST ke endpoint ini ketika:
     * - Payment status berubah (pending → paid)
     * - Invoice expired
     * - Payment failed
     * 
     * Security Flow:
     * 1. Log webhook attempt
     * 2. Verify signature
     * 3. Check for replay attack
     * 4. Process webhook
     * 5. Update log status
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function xendit(Request $request)
    {
        $startTime = microtime(true);
        $webhookData = $request->all();
        $externalId = $webhookData['external_id'] ?? null;

        // 1. Create webhook log entry
        $webhookLog = WebhookLog::create([
            'source' => 'xendit',
            'event_type' => $webhookData['status'] ?? 'unknown',
            'external_id' => $externalId,
            'payload' => $webhookData,
            'headers' => [
                // SECURITY: Mask sensitive token (only store first 10 chars for debugging)
                'x-callback-token' => substr($request->header('x-callback-token', ''), 0, 10) . '...',
                'x-signature' => $request->header('x-signature'),
                'content-type' => $request->header('content-type'),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'failed', // Default to failed, will update if successful
        ]);

        // Log to payment channel
        Log::channel('payment')->info('Xendit webhook received', [
            'webhook_log_id' => $webhookLog->id,
            'ip' => $request->ip(),
            'external_id' => $externalId,
        ]);

        try {
            // 2. Check for brute force attacks (too many failed attempts)
            $recentFailedAttempts = WebhookLog::getRecentFailedAttempts($request->ip(), 5);
            
            if ($recentFailedAttempts >= 10) {
                $webhookLog->update([
                    'status' => 'invalid',
                    'error_message' => 'Too many failed attempts - IP temporarily blocked',
                ]);

                Log::channel('payment')->warning('Brute force attack detected', [
                    'webhook_log_id' => $webhookLog->id,
                    'ip' => $request->ip(),
                    'failed_attempts' => $recentFailedAttempts,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests',
                ], 429);
            }

            // 3. Verify webhook signature
            $webhookToken = $request->header('x-callback-token');
            
            if (!$webhookToken) {
                $webhookLog->update([
                    'status' => 'invalid',
                    'error_message' => 'Missing x-callback-token header',
                ]);

                Log::channel('payment')->warning('Webhook without token', [
                    'webhook_log_id' => $webhookLog->id,
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Verify signature
            $isValid = $this->paymentService->verifyWebhookSignature(
                $webhookToken,
                $request->header('x-signature', ''),
                $request->getContent()
            );

            if (!$isValid) {
                $webhookLog->update([
                    'status' => 'invalid',
                    'error_message' => 'Invalid webhook signature',
                ]);

                Log::channel('payment')->warning('Invalid webhook signature', [
                    'webhook_log_id' => $webhookLog->id,
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            // 4. Check for replay attack
            if ($externalId && WebhookLog::isReplayAttack($externalId, 'xendit')) {
                $webhookLog->update([
                    'status' => 'invalid',
                    'error_message' => 'Replay attack detected - external_id already processed',
                ]);

                Log::channel('payment')->warning('Replay attack detected', [
                    'webhook_log_id' => $webhookLog->id,
                    'external_id' => $externalId,
                    'ip' => $request->ip(),
                ]);

                // Return 200 to prevent Xendit retry (it's already processed)
                return response()->json([
                    'success' => true,
                    'message' => 'Already processed',
                ]);
            }

            // 5. Process webhook
            $result = $this->paymentService->handleWebhook($webhookData);

            if ($result) {
                // Update log as successful
                $webhookLog->update([
                    'status' => 'success',
                    'processed_at' => now(),
                    'error_message' => null,
                ]);

                $processingTime = round((microtime(true) - $startTime) * 1000, 2);

                Log::channel('payment')->info('Webhook processed successfully', [
                    'webhook_log_id' => $webhookLog->id,
                    'external_id' => $externalId,
                    'processing_time_ms' => $processingTime,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ]);
            } else {
                $webhookLog->update([
                    'status' => 'failed',
                    'error_message' => 'Webhook processing returned false',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Webhook processing failed',
                ], 400);
            }

        } catch (\Exception $e) {
            // Update log with error
            $webhookLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::channel('payment')->error('Webhook processing error', [
                'webhook_log_id' => $webhookLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 200 to prevent Xendit retry
            // (we already logged the error for manual investigation)
            return response()->json([
                'success' => false,
                'message' => 'Internal error',
            ], 200);
        }
    }
}
