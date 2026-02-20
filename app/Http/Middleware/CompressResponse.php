<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     * 
     * Compress response with gzip if client supports it
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if:
        // 1. Client accepts gzip encoding
        // 2. Response is successful (2xx)
        // 3. Content is compressible (JSON, HTML, etc)
        // 4. Content is large enough (> 1KB)
        
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        if (!$response->isSuccessful()) {
            return $response;
        }

        $content = $response->getContent();
        
        if (empty($content) || strlen($content) < 1024) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'application/json',
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/xml',
            'text/xml',
        ];

        $isCompressible = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $isCompressible = true;
                break;
            }
        }

        if (!$isCompressible) {
            return $response;
        }

        // Compress the content
        $compressed = gzencode($content, 6); // Level 6 is good balance between speed and compression

        if ($compressed === false) {
            return $response;
        }

        // Set compressed content and headers
        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressed));
        $response->headers->remove('Transfer-Encoding'); // Remove if exists

        return $response;
    }
}
