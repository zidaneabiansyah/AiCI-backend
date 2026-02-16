<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::query();
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        $facilities = $query->orderBy('order')->get();
        
        return response()->json([
            'success' => true,
            'results' => $facilities,
        ]);
    }
    
    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $facility,
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:RUANGAN,MODUL,MEDIA_KIT,ROBOT',
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
            $data['image'] = $request->file('image')->store('facilities', 'public');
        }
        
        $facility = Facility::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $facility,
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|in:RUANGAN,MODUL,MEDIA_KIT,ROBOT',
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
            if ($facility->image) {
                Storage::disk('public')->delete($facility->image);
            }
            $data['image'] = $request->file('image')->store('facilities', 'public');
        }
        
        $facility->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $facility,
        ]);
    }
    
    public function destroy($id)
    {
        $facility = Facility::findOrFail($id);
        
        if ($facility->image) {
            Storage::disk('public')->delete($facility->image);
        }
        
        $facility->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Facility deleted',
        ]);
    }
    
    public function reorder(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            Facility::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
}
