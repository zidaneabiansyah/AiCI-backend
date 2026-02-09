<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom exception for payment-related errors
 */
class PaymentException extends Exception
{

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
