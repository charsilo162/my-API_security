<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use OpenAI\Laravel\Facades\OpenAI;
// use App\Models\BlogPost;
// use App\Models\SafetyTerm; // If using
// use Illuminate\Support\Str;

// class GenerateBlogPosts extends Command
// {
//     protected $signature = 'blog:generate';
//     protected $description = 'Generate AI blog post weekly';

//     public function handle()
//     {
//         // Example topics; load from config or DB for variety
//         $topics = ['Event Security Tips', 'Corporate Protection Strategies', 'Personal Safety Advice'];
//         $topic = $topics[array_rand($topics)];

//         // AI Prompt for post
//         $postResponse = OpenAI::completions()->create([
//             'model' => 'gpt-4o', // Or your model
//             'prompt' => "Generate an 800-word blog post on '{$topic}' for a security blog. Include title, excerpt (100 words), content, and estimated read time. Focus on practical security advice.",
//             'max_tokens' => 1200,
//         ]);
//         $generatedPost = $postResponse['choices'][0]['text'];

        // Parse the response (simplified; use regex or string manipulation for real)
        // $title = Str::before($generatedPost, "\n"); // Assume first line is title
        // $excerpt = Str::after(Str::before($generatedPost, "\n\n\n"), "\n\n"); // Adjust based on format
        // $content = $generatedPost; // Full
        // $readTime = round(str_word_count($content) / 200);

        // Create post
        // $post = BlogPost::create([
        //     'title' => $title,
        //     'excerpt' => $excerpt,
        //     'content' => $content,
        //     'published_at' => now(),
        //     'read_time' => $readTime,
        //     'category' => $topic,
        //     'status' => 'draft', // Review before publish
        //     'image' => $this->fetchImage($topic), // Optional: Implement Unsplash integration
        // ]);

        // Generate safety terms
        // $termsResponse = OpenAI::completions()->create([
        //     'model' => 'gpt-4o',
        //     'prompt' => "List 5 safety terms related to '{$topic}' with short definitions.",
        //     'max_tokens' => 300,
        // ]);
        // $generatedTerms = $termsResponse['choices'][0]['text'];

        // Parse and save (e.g., split by lines)
    //     foreach (explode("\n", $generatedTerms) as $line) {
    //         if (Str::contains($line, ':')) {
    //             [$term, $definition] = explode(':', $line, 2);
    //             SafetyTerm::create([
    //                 'term' => trim($term),
    //                 'definition' => trim($definition),
    //                 'blog_post_id' => $post->id,
    //             ]);
    //         }
    //     }

    //     $this->info('New blog post generated: ' . $title);
    // }

    // private function fetchImage($topic)
    // {
    //     // Optional: Use Guzzle to fetch from Unsplash API
    //     // Return path after storing
    //     return '/storage/images/default.jpg'; // Placeholder
    // }
// }