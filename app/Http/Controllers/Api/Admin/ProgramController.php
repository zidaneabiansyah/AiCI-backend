<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('order')->get();
        
        return response()->json([
            'success' => true,
            'results' => $programs,
        ]);
    }
    
    public function show($id)
    {
        $program = Program::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('programs', 'public');
        }
        
        $program = Program::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $program,
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('image')) {
            if ($program->image) {
                Storage::disk('public')->delete($program->image);
            }
            $data['image'] = $request->file('image')->store('programs', 'public');
        }
        
        $program->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }
    
    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        
        if ($program->image) {
            Storage::disk('public')->delete($program->image);
        }
        
        $program->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Program deleted',
        ]);
    }
    
    public function reorder(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            Program::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
}
