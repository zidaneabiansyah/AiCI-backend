<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index()
    {
        $classes = ClassModel::with(['program'])
            ->withCount(['enrollments', 'schedules'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $programs = Program::where('is_active', true)->get();
        return Inertia::render('Admin/Classes/Create', [
            'programs' => $programs,
        ]);
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|string',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ClassModel::create($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassModel $class)
    {
        $programs = Program::where('is_active', true)->get();
        return Inertia::render('Admin/Classes/Edit', [
            'class' => $class,
            'programs' => $programs,
        ]);
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|string',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($request->name !== $class->name) {
            $validated['slug'] = Str::slug($request->name);
        }

        $class->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassModel $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }
}
