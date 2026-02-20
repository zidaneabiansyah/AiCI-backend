<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk Class browsing & detail
 * 
 * Endpoints:
 * - GET /classes - List all classes dengan filtering
 * - GET /classes/{class} - Show class detail
 */
class ClassController extends BaseController
{
    /**
     * Display list of classes
     * 
     * Features:
     * - Filter by program
     * - Filter by level
     * - Filter by education level
     * - Search by name
     * - Sort by price, name, popularity
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = ClassModel::select('id', 'program_id', 'name', 'slug', 'code', 'level', 'description', 'price', 'duration_hours', 'capacity', 'enrolled_count', 'is_featured', 'image', 'sort_order')
            ->with(['program:id,name,slug,education_level'])
            ->active();

        // Filter by program
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Filter by education level (via program)
        if ($request->has('education_level')) {
            $query->whereHas('program', function ($q) use ($request) {
                $q->where('education_level', $request->education_level);
            });
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } elseif ($sortBy === 'popularity') {
            $query->orderBy('enrolled_count', 'desc');
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }

        // Paginate
        $classes = $query->paginate(12);

        // Get programs for filter
        $programs = Program::select('id', 'name', 'slug', 'education_level', 'sort_order')
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('Classes/Index', [
            'classes' => $classes->through(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'slug' => $class->slug,
                    'code' => $class->code,
                    'level' => $class->level,
                    'description' => $class->description,
                    'price' => $class->price,
                    'price_formatted' => formatCurrency($class->price),
                    'duration_hours' => $class->duration_hours,
                    'capacity' => $class->capacity,
                    'enrolled_count' => $class->enrolled_count,
                    'remaining_slots' => $class->getRemainingSlots(),
                    'is_featured' => $class->is_featured,
                    'image' => $class->image,
                    'program' => [
                        'id' => $class->program->id,
                        'name' => $class->program->name,
                        'education_level' => $class->program->education_level,
                    ],
                ];
            }),
            'programs' => $programs,
            'filters' => $request->only(['program_id', 'level', 'education_level', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show class detail
     * 
     * @param ClassModel $class
     * @return \Inertia\Response
     */
    public function show(ClassModel $class)
    {
        // Load relationships with specific columns
        $class->load([
            'program:id,name,slug,education_level,description',
            'schedules' => function ($query) {
                $query->select('id', 'class_id', 'batch_name', 'start_date', 'end_date', 'day_of_week', 'start_time', 'end_time', 'location', 'instructor_name', 'capacity', 'enrolled_count', 'is_available')
                    ->available()
                    ->orderBy('start_date');
            }
        ]);

        // Check if user can enroll
        $canEnroll = ['can_enroll' => true, 'reason' => null];
        if (auth()->check()) {
            $enrollmentService = app(\App\Services\EnrollmentService::class);
            $canEnroll = $enrollmentService->canEnroll(auth()->user(), $class);
        }

        return Inertia::render('Classes/Show', [
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'slug' => $class->slug,
                'code' => $class->code,
                'level' => $class->level,
                'description' => $class->description,
                'curriculum' => $class->curriculum,
                'prerequisites' => $class->prerequisites,
                'min_score' => $class->min_score,
                'min_age' => $class->min_age,
                'max_age' => $class->max_age,
                'duration_hours' => $class->duration_hours,
                'total_sessions' => $class->total_sessions,
                'price' => $class->price,
                'price_formatted' => formatCurrency($class->price),
                'capacity' => $class->capacity,
                'enrolled_count' => $class->enrolled_count,
                'remaining_slots' => $class->getRemainingSlots(),
                'image' => $class->image,
                'program' => [
                    'id' => $class->program->id,
                    'name' => $class->program->name,
                    'slug' => $class->program->slug,
                    'education_level' => $class->program->education_level,
                    'description' => $class->program->description,
                ],
            ],
            'schedules' => $class->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'batch_name' => $schedule->batch_name,
                    'start_date' => $schedule->start_date,
                    'end_date' => $schedule->end_date,
                    'start_date_formatted' => formatDate($schedule->start_date),
                    'end_date_formatted' => formatDate($schedule->end_date),
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'instructor_name' => $schedule->instructor_name,
                    'capacity' => $schedule->capacity,
                    'enrolled_count' => $schedule->enrolled_count,
                    'remaining_slots' => $schedule->getRemainingSlots(),
                    'is_available' => $schedule->is_available,
                ];
            }),
            'canEnroll' => $canEnroll,
        ]);
    }
}
