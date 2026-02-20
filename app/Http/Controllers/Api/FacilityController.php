<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends BaseController
{
    /**
     * Display a listing of facilities
     * 
     * GET /api/v1/facilities
     */
    public function index()
    {
        $facilities = Facility::select('id', 'name', 'slug', 'description', 'type', 'quantity', 'specifications', 'sort_order')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse(
            FacilityResource::collection($facilities),
            'Facilities retrieved successfully'
        );
    }

    /**
     * Display the specified facility
     * 
     * GET /api/v1/facilities/{id}
     */
    public function show(Facility $facility)
    {
        return $this->successResponse(
            new FacilityResource($facility),
            'Facility details retrieved successfully'
        );
    }
}
