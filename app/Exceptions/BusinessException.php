<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom exception for business logic errors
 */
class BusinessException extends Exception
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
            ], $this->getCode());
        }

        return back()
            ->with('error', $this->getMessage())
            ->withInput();
    }
}
