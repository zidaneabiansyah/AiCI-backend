<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProgramController extends Controller
{
    /**
     * Display a listing of the programs.
     */
    public function index()
    {
        $programs = Program::withCount('classes')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Programs/Index', [
            'programs' => $programs,
        ]);
    }

    /**
     * Show the form for creating a new program.
     */
    public function create()
    {
        return Inertia::render('Admin/Programs/Create');
    }

    /**
     * Store a newly created program in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|gt:min_age',
            'is_active' => 'boolean',
            'image' => 'nullable|string', // Placeholder for image upload logic
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Program::create($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program created successfully.');
    }

    /**
     * Show the form for editing the specified program.
     */
    public function edit(Program $program)
    {
        return Inertia::render('Admin/Programs/Edit', [
            'program' => $program,
        ]);
    }

    /**
     * Update the specified program in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|gt:min_age',
            'is_active' => 'boolean',
            'image' => 'nullable|string',
        ]);

        if ($request->name !== $program->name) {
            $validated['slug'] = Str::slug($request->name);
        }

        $program->update($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated successfully.');
    }

    /**
     * Remove the specified program from storage.
     */
    public function destroy(Program $program)
    {
        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}
