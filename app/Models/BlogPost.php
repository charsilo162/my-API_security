<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'read_time',
        'published_at',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            $post->uuid = Str::uuid();
            $post->slug = Str::slug($post->title);
        });
    }
    
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // public function categories()
    // {
    //     return $this->belongsToMany(BlogCategory::class);
    // }
    public function categories()
        {
            return $this->belongsToMany(BlogCategory::class, 'blog_category_post');
        }
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

            public function publish()
        {
            $this->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        public function unpublish()
        {
            $this->update([
                'status' => 'draft',
            ]);
        }
}
