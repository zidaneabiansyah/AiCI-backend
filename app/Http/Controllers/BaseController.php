<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

abstract class BaseController extends Controller
{
    /**
     * Return success JSON response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error JSON response
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return redirect with success message
     */
    protected function redirectWithSuccess(
        string $route,
        string $message = 'Operation successful',
        array $parameters = []
    ): RedirectResponse {
        return redirect()
            ->route($route, $parameters)
            ->with('success', $message);
    }

    /**
     * Return redirect with error message
     */
    protected function redirectWithError(
        string $route,
        string $message = 'Operation failed',
        array $parameters = []
    ): RedirectResponse {
        return redirect()
            ->route($route, $parameters)
            ->with('error', $message);
    }

    /**
     * Return back with success message
     */
    protected function backWithSuccess(string $message = 'Operation successful'): RedirectResponse
    {
        return back()->with('success', $message);
    }

    /**
     * Return back with error message
     */
    protected function backWithError(string $message = 'Operation failed'): RedirectResponse
    {
        return back()->with('error', $message);
    }
}
