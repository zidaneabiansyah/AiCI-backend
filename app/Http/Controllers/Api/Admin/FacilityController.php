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
            $query->where('type', $request->category);
        }
        
        $facilities = $query->orderBy('sort_order')->get()->map(function ($facility) {
            return [
                'id' => $facility->id,
                'category' => $facility->type,
                'category_display' => ucfirst(strtolower($facility->type)),
                'title' => $facility->name,
                'description' => $facility->description,
                'image' => $facility->image ? '/storage/' . $facility->image : null,
                'order' => $facility->sort_order,
            ];
        });
        
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
            'data' => [
                'id' => $facility->id,
                'category' => $facility->type,
                'category_display' => ucfirst(strtolower($facility->type)),
                'title' => $facility->name,
                'description' => $facility->description,
                'image' => $facility->image ? '/storage/' . $facility->image : null,
                'order' => $facility->sort_order,
            ],
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
        
        $mappedData = [
            'type' => $data['category'],
            'name' => $data['title'],
            'description' => $data['description'],
            'sort_order' => $data['order'] ?? 0,
        ];
        
        if ($request->hasFile('image')) {
            $mappedData['image'] = $request->file('image')->store('facilities', 'public');
        }
        
        $facility = Facility::create($mappedData);
        
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
        
        $mappedData = [];
        if (isset($data['category'])) $mappedData['type'] = $data['category'];
        if (isset($data['title'])) $mappedData['name'] = $data['title'];
        if (isset($data['description'])) $mappedData['description'] = $data['description'];
        if (isset($data['order'])) $mappedData['sort_order'] = $data['order'];
        
        if ($request->hasFile('image')) {
            if ($facility->image) {
                Storage::disk('public')->delete($facility->image);
            }
            $mappedData['image'] = $request->file('image')->store('facilities', 'public');
        }
        
        $facility->update($mappedData);
        
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
            Facility::where('id', $id)->update(['sort_order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
}
