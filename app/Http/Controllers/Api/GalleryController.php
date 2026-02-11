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
        $query = Gallery::where('is_active', true);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $perPage = $request->integer('per_page', 20);
        $galleries = $query->orderBy('sort_order')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(
            GalleryResource::collection($galleries)->response()->getData(true),
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
