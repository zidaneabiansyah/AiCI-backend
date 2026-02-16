<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::query();
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }
        
        $images = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'results' => $images,
        ]);
    }
    
    public function show($id)
    {
        $image = Gallery::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $image,
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'category' => 'required|in:KEGIATAN,FASILITAS,KOMPETISI,LAINNYA',
            'description' => 'nullable|string',
            'date_taken' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
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
            $data['image'] = $request->file('image')->store('gallery', 'public');
        }
        
        $gallery = Gallery::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $gallery,
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
            'category' => 'sometimes|in:KEGIATAN,FASILITAS,KOMPETISI,LAINNYA',
            'description' => 'nullable|string',
            'date_taken' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
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
            if ($gallery->image) {
                Storage::disk('public')->delete($gallery->image);
            }
            $data['image'] = $request->file('image')->store('gallery', 'public');
        }
        
        $gallery->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    }
    
    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);
        
        if ($gallery->image) {
            Storage::disk('public')->delete($gallery->image);
        }
        
        $gallery->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Gallery image deleted',
        ]);
    }
}
