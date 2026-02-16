<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Get all schedules
     */
    public function index(Request $request)
    {
        $query = ClassSchedule::with('class');

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $schedules = $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'results' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'class_id' => $schedule->class_id,
                    'class_name' => $schedule->class->name ?? 'N/A',
                    'batch_name' => $schedule->batch_name,
                    'start_date' => $schedule->start_date->format('Y-m-d'),
                    'end_date' => $schedule->end_date->format('Y-m-d'),
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'instructor_name' => $schedule->instructor_name,
                    'capacity' => $schedule->capacity,
                    'enrolled_count' => $schedule->enrolled_count,
                    'is_available' => $schedule->is_available,
                    'notes' => $schedule->notes,
                ];
            }),
        ]);
    }

    /**
     * Get single schedule
     */
    public function show($id)
    {
        $schedule = ClassSchedule::with('class')->findOrFail($id);

        return response()->json([
            'id' => $schedule->id,
            'class_id' => $schedule->class_id,
            'class_name' => $schedule->class->name ?? 'N/A',
            'batch_name' => $schedule->batch_name,
            'start_date' => $schedule->start_date->format('Y-m-d'),
            'end_date' => $schedule->end_date->format('Y-m-d'),
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'location' => $schedule->location,
            'instructor_name' => $schedule->instructor_name,
            'capacity' => $schedule->capacity,
            'enrolled_count' => $schedule->enrolled_count,
            'is_available' => $schedule->is_available,
            'notes' => $schedule->notes,
        ]);
    }

    /**
     * Create schedule
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'batch_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'instructor_name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_available' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $schedule = ClassSchedule::create([
            'class_id' => $request->class_id,
            'batch_name' => $request->batch_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'instructor_name' => $request->instructor_name,
            'capacity' => $request->capacity,
            'enrolled_count' => 0,
            'is_available' => $request->is_available ?? true,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Schedule created successfully',
            'data' => $schedule,
        ], 201);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = ClassSchedule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'class_id' => 'sometimes|exists:classes,id',
            'batch_name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'day_of_week' => 'sometimes|string',
            'start_time' => 'sometimes|string',
            'end_time' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'instructor_name' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'is_available' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $schedule->update($request->only([
            'class_id',
            'batch_name',
            'start_date',
            'end_date',
            'day_of_week',
            'start_time',
            'end_time',
            'location',
            'instructor_name',
            'capacity',
            'is_available',
            'notes',
        ]));

        return response()->json([
            'message' => 'Schedule updated successfully',
            'data' => $schedule,
        ]);
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        $schedule = ClassSchedule::findOrFail($id);

        // Check if schedule has enrollments
        if ($schedule->enrolled_count > 0) {
            return response()->json([
                'message' => 'Cannot delete schedule with existing enrollments',
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully',
        ]);
    }
}
