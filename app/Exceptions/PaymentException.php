<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom exception for payment-related errors
 */
class PaymentException extends Exception
{
    /**
     * Create a new payment exception instance
     */
    public function __construct(
        string $message = 'Payment processing error',
        int $code = 500,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => 'payment_error',
            ], $this->getCode());
        }

        return back()
            ->with('error', 'Payment Error: ' . $this->getMessage())
            ->withInput();
    }
}
