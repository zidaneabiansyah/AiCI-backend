<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ClassResource;
use App\Models\ClassModel;
use Illuminate\Http\Request;

/**
 * Class API Controller
 * 
 * Public API untuk mobile app
 * Endpoints untuk list dan detail kelas
 */
class ClassController extends BaseController
{
    /**
     * Display a listing of classes
     * 
     * GET /api/v1/classes
     * 
     * Query params:
     * - program_id: filter by program
     * - level: filter by level
     * - is_active: filter by active status (default: true)
     * - with_schedules: include schedules
     */
    public function index(Request $request)
    {
        $query = ClassModel::with('program');

        // Filter by program
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Filter by active status
        $isActive = $request->boolean('is_active', true);
        if ($isActive) {
            $query->active();
        }

        // Include schedules if requested
        if ($request->boolean('with_schedules')) {
            $query->with(['schedules' => function ($q) {
                $q->available()->orderBy('start_date');
            }]);
        }

        $classes = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            ClassResource::collection($classes),
            'Classes retrieved successfully'
        );
    }

    /**
     * Display the specified class
     * 
     * GET /api/v1/classes/{slug}
     */
    public function show(string $slug)
    {
        $class = ClassModel::where('slug', $slug)
            ->with([
                'program',
                'schedules' => function ($q) {
                    $q->available()->orderBy('start_date');
                }
            ])
            ->firstOrFail();

        return $this->successResponse(
            new ClassResource($class),
            'Class details retrieved successfully'
        );
    }
}
