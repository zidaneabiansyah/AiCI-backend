<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom exception for business logic errors
 */
class BusinessException extends Exception
{
    /**
     * Create a new business exception instance
     */
    public function __construct(
        string $message = 'Business logic error',
        int $code = 400,
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
            ], $this->getCode());
        }

        return back()
            ->with('error', $this->getMessage())
            ->withInput();
    }
}
