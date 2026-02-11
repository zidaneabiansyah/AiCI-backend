<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Facility;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ContentController extends Controller
{
    /**
     * Display Articles Index.
     */
    public function articles()
    {
        $articles = Article::latest()->paginate(10);
        return Inertia::render('Admin/Content/Articles/Index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Display Gallery Index.
     */
    public function gallery()
    {
        $galleries = Gallery::latest()->paginate(10);
        return Inertia::render('Admin/Content/Gallery/Index', [
            'galleries' => $galleries,
        ]);
    }

    /**
     * Display Facilities Index.
     */
    public function facilities()
    {
        $facilities = Facility::latest()->paginate(10);
        return Inertia::render('Admin/Content/Facilities/Index', [
            'facilities' => $facilities,
        ]);
    }

    // Since I need to implement CRUD for all 3, I'll create separate controllers if they get too big.
    // But for now, I'll just provide the structure for Articles as a priority.
}
