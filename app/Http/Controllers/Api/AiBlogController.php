<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\BlogPost;
use App\Services\BlogAIService;
use Illuminate\Http\Request;
  use App\Models\BlogCategory; // Ensure you import the Category model
use Illuminate\Support\Str;
class AiBlogController extends Controller
{


public function generate(Request $request, BlogAIService $ai)
{
               \Log::info('===  REQUEST START Ai===', [
    

        'input'     => $request->all(),
       
    ]);
    $validated = $request->validate([
        'topic' => 'required|string|min:5',
    ]);

    $postsData = $ai->generateBatchBlogs($validated['topic']);

    if (!$postsData) {
        return response()->json(['message' => 'Failed to generate content'], 500);
    }

    $createdPosts = [];

    foreach ($postsData as $item) {
        // 1. Create or Find the Category
        $categoryName = $item['category'] ?? 'Uncategorized';
        $category = BlogCategory::firstOrCreate(
            ['name' => $categoryName],
            ['slug' => Str::slug($categoryName)]
        );

        // 2. Create the Blog Post
        $post = BlogPost::create([
            'uuid'    => Str::uuid(),
            'title'   => $item['title'],
            'slug'    => Str::slug($item['title']) . '-' . rand(100, 999),
            'excerpt' => substr(strip_tags($item['summary'] ?? ''), 0, 160),
            'content' => $item['content'] . "\n\n" . ($item['conclusion'] ?? ''),
            'status'  => 'draft',
            //'featured_image'   => $item['image_url'] ?? null,
        ]);

        // 3. Populate the pivot table (blog_category_post)
        $post->categories()->attach($category->id);

        $createdPosts[] = $post->load('categories');
    }

    return response()->json([
        'message' => '5 posts and their categories generated.',
        'posts'   => $createdPosts
    ], 201);
}
}