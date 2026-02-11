<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;

/**
 * Program API Controller
 * 
 * Public API untuk mobile app
 * Endpoints untuk list dan detail program
 */
class ProgramController extends BaseController
{
    /**
     * Display a listing of programs
     * 
     * GET /api/v1/programs
     * 
     * Query params:
     * - education_level: filter by education level
     * - is_active: filter by active status (default: true)
     * - with_classes: include classes relationship
     */
    public function index(Request $request)
    {
        $query = Program::query();

        // Filter by education level
        if ($request->has('education_level')) {
            $query->where('education_level', $request->education_level);
        }

        // Filter by active status (default: only active)
        $isActive = $request->boolean('is_active', true);
        if ($isActive) {
            $query->active();
        }

        // Include classes if requested
        if ($request->boolean('with_classes')) {
            $query->with(['classes' => function ($q) {
                $q->active()->orderBy('sort_order');
            }]);
        }

        $programs = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            ProgramResource::collection($programs),
            'Programs retrieved successfully'
        );
    }

    /**
     * Display the specified program
     * 
     * GET /api/v1/programs/{slug}
     */
    public function show(string $slug)
    {
        $program = Program::where('slug', $slug)
            ->with([
                'classes' => function ($q) {
                    $q->active()
                        ->with('schedules')
                        ->orderBy('sort_order');
                }
            ])
            ->firstOrFail();

        return $this->successResponse(
            new ProgramResource($program),
            'Program details retrieved successfully'
        );
    }
}
