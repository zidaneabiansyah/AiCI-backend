<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        $articles = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'results' => $articles,
        ]);
    }
    
    public function show($id)
    {
        $article = Article::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'thumbnail' => 'required|image|max:2048',
            'author' => 'required|string|max:255',
            'published_at' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['title']);
        
        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Article::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }
        
        $article = Article::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $article,
        ], 201);
    }
    
    public function update(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'sometimes|string',
            'content' => 'sometimes|string',
            'thumbnail' => 'nullable|image|max:2048',
            'author' => 'sometimes|string|max:255',
            'published_at' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $article->title) {
            $newSlug = Str::slug($data['title']);
            $originalSlug = $newSlug;
            $counter = 1;
            while (Article::where('slug', $newSlug)->where('id', '!=', $article->id)->exists()) {
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $newSlug;
        }
        
        if ($request->hasFile('thumbnail')) {
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }
        
        $article->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }
    
    public function destroy($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }
        
        $article->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Article deleted',
        ]);
    }
}
