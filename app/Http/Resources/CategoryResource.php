<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            // Pivot info (optional but useful)
            'pivot' => $this->whenPivotLoaded('blog_category_post', function () {
                return [
                    'blog_post_id'     => $this->pivot->blog_post_id,
                    'blog_category_id' => $this->pivot->blog_category_id,
                ];
            }),
        ];
    }
}

