<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /** Public list of categories with published-course counts. */
    public function index()
    {
        $categories = Category::orderBy('sort_order')
            ->withCount(['courses' => fn ($q) => $q->where('status', 'published')])
            ->get();

        return response()->json(['categories' => $categories]);
    }
}
