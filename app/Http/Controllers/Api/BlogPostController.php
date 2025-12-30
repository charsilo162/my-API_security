<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Enums\PostStatus;
use App\Http\Resources\BlogPostResource;
use App\Http\Resources\BlogCategoryResource;

class BlogPostController extends Controller
{
    /* =======================
       PUBLIC + ADMIN INDEX
       ======================= */
    public function index(Request $request)
    {
        $query = BlogPost::query()->with('categories');

        // 🔐 If NOT authenticated → public user
        if (! auth('sanctum')->check()) {
            $query->where('status', PostStatus::PUBLISHED->value);
        }

        // 🔎 Search
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        // 🏷 Status filter (admin only)
        if (
            auth('sanctum')->check() &&
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where('status', $request->status);
        }

        return BlogPostResource::collection(
            $query->latest()->paginate(10)
        );
    }

    /* =======================
       PUBLIC + ADMIN SHOW
       ======================= */
    public function show(BlogPost $post)
    {
        // Public users can ONLY see published posts
        if (! auth('sanctum')->check()) {
            abort_if(
                $post->status !== PostStatus::PUBLISHED->value,
                404
            );
        }

        return new BlogPostResource(
            $post->load('categories')
        );
    }

    /* =======================
       ADMIN ONLY
       ======================= */

    public function store(Request $request)
    {
        $this->authorize('create', BlogPost::class);

        $data = $request->validate([
            'title'   => 'required|string|min:5',
            'content' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'exists:blog_categories,id',
        ]);

        $post = BlogPost::create([
            'title'   => $data['title'],
            'content' => $data['content'],
            'status'  => PostStatus::DRAFT->value,
        ]);

        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        return new BlogPostResource($post->load('categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'title'   => 'required|string|min:5',
            'content' => 'required|string',
             'categories' => 'present|array|max:1',
            'categories.*' => 'exists:blog_categories,id',
        ]);

        $post->update([
            'title'   => $data['title'],
            'content' => $data['content'],
        ]);

        if (array_key_exists('categories', $data)) {
            $post->categories()->sync($data['categories']);
        }

        return response()->json([
            'message' => 'Post updated successfully',
            'data'    => new BlogPostResource($post->load('categories')),
        ]);
    }

    // public function destroy(BlogPost $post)
    // {
    //     $this->authorize('delete', $post);

    //     $post->delete();

    //     return response()->json(['message' => 'Post deleted']);
    // }
public function destroy(BlogPost $post)
    {
        $this->authorize('delete', $post);

        // Detach categories before deleting if not using cascading deletes
        $post->categories()->detach();
        
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
    /* =======================
       STATE CHANGES (ADMIN)
       ======================= */

    public function publish(BlogPost $post)
    {
        $this->authorize('publish', $post);

        $post->update([
            'status' => PostStatus::PUBLISHED->value,
            'published_at' => now(),
        ]);

        return response()->json(['message' => 'Post published']);
    }

    public function unpublish(BlogPost $post)
    {
        $this->authorize('publish', $post);

        $post->update([
            'status' => PostStatus::UNPUBLISHED->value,
        ]);

        return response()->json(['message' => 'Post unpublished']);
    }
}