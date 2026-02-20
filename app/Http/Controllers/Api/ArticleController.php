<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * Article API Controller
 * 
 * Public API untuk mobile app
 */
class ArticleController extends BaseController
{
    /**
     * Display a listing of articles
     * 
     * GET /api/v1/articles
     * 
     * Query params:
     * - category: filter by category
     * - search: search in title/content
     * - per_page: pagination (default: 15)
     */
    public function index(Request $request)
    {
        $query = Article::select('id', 'title', 'slug', 'excerpt', 'thumbnail', 'category', 'published_at', 'views_count', 'created_at')
            ->published();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->integer('per_page', 15), 50); // Max 50 items per page
        $articles = $query->latest('published_at')
            ->paginate($perPage);

        return $this->successResponse(
            ArticleResource::collection($articles)->response()->getData(true),
            'Articles retrieved successfully'
        );
    }

    /**
     * Display the specified article
     * 
     * GET /api/v1/articles/{slug}
     */
    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment views
        $article->increment('views_count');

        return $this->successResponse(
            new ArticleResource($article),
            'Article details retrieved successfully'
        );
    }
}
