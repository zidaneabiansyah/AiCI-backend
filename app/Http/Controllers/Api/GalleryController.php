<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends BaseController
{
    /**
     * Display a listing of gallery items
     * 
     * GET /api/v1/galleries
     * 
     * Query params:
     * - category: filter by category
     * - per_page: pagination (default: 20)
     */
    public function index(Request $request)
    {
        $query = Gallery::select('id', 'title', 'description', 'image', 'category', 'event_date', 'is_featured', 'sort_order')
            ->where('is_active', true);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by featured
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $galleries = $query->orderBy('sort_order')
            ->latest('event_date')
            ->get();

        return $this->successResponse(
            GalleryResource::collection($galleries),
            'Galleries retrieved successfully'
        );
    }

    /**
     * Display the specified gallery item
     * 
     * GET /api/v1/galleries/{id}
     */
    public function show(Gallery $gallery)
    {
        return $this->successResponse(
            new GalleryResource($gallery),
            'Gallery item details retrieved successfully'
        );
    }
}
