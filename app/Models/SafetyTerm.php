<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyTerm extends Model
{
    use HasFactory;

    protected $fillable = ['term', 'definition', 'blog_post_id'];

    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class);
    }
}