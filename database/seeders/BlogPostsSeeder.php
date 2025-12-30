<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogPostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BlogPost::create([
            'title' => 'Sample Post',
            'content' => 'This is a sample AI-generated post...',
            'excerpt' => 'Short summary...',
            'image' => '/storage/images/sample.jpg',
            'published_at' => now(),
            'read_time' => 3,
            'category' => 'Security Tips',
            'status' => 'published',
        ]);
    }
}
