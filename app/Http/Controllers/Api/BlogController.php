<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Http\Resources\BlogPostResource; // We'll create this next
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = BlogPost::where('status', 'published')->latest('published_at');

    //     if ($search = $request->query('search')) {
    //         $query->where('title', 'like', "%{$search}%")
    //               ->orWhere('content', 'like', "%{$search}%");
    //     }

    //     if ($category = $request->query('category')) {
    //         $query->where('category', $category);
    //     }

    //     $posts = $query->paginate(10);

    //     return BlogPostResource::collection($posts);
    // }

    // public function show(BlogPost $blogPost)
    // {
    //     if ($blogPost->status !== 'published') {
    //         abort(404);
    //     }

    //     return new BlogPostResource($blogPost);
    // }

    // Additional methods for store/update/delete if needed for admin
}