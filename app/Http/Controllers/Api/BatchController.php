<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Batch API Controller
 * 
 * Allows fetching multiple resources in a single request
 * Reduces waterfall requests and improves frontend performance
 * 
 * Example request:
 * POST /api/v1/batch
 * {
 *   "requests": [
 *     { "method": "GET", "url": "/api/v1/programs" },
 *     { "method": "GET", "url": "/api/v1/articles?category=news" },
 *     { "method": "GET", "url": "/api/v1/facilities" }
 *   ]
 * }
 */
class BatchController extends BaseController
{
    /**
     * Process batch requests
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requests' => 'required|array|max:10',
            'requests.*.method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'requests.*.url' => 'required|string|max:500',
            'requests.*.body' => 'nullable|array',
        ]);

        $results = [];
        
        foreach ($validated['requests'] as $index => $batchRequest) {
            try {
                // Parse the URL to get path and query params
                $urlParts = parse_url($batchRequest['url']);
                $path = $urlParts['path'];
                $queryString = $urlParts['query'] ?? '';
                
                // Create a new request for each batch item
                $subRequest = Request::create(
                    $path . ($queryString ? '?' . $queryString : ''),
                    $batchRequest['method'],
                    $batchRequest['body'] ?? [],
                    [],
                    [],
                    array_merge($_SERVER, [
                        'HTTP_AUTHORIZATION' => $request->header('Authorization'),
                        'HTTP_ACCEPT' => $request->header('Accept'),
                    ])
                );
                
                // Copy authenticated user
                if ($request->user()) {
                    $subRequest->setUserResolver(fn () => $request->user());
                }
                
                // Route the request
                $response = app()->handle($subRequest);
                
                // Parse response
                $statusCode = $response->getStatusCode();
                $content = $response->getContent();
                
                try {
                    $data = json_decode($content, true);
                } catch (\Exception $e) {
                    $data = $content;
                }
                
                $results[] = [
                    'index' => $index,
                    'status' => $statusCode,
                    'data' => $data,
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'status' => 500,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Batch requests processed',
            'results' => $results,
        ]);
    }
}
