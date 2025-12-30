<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogPostPolicy
{
    public function update(User $user, BlogPost $post): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, BlogPost $post): bool
    {
        return $user->isAdmin();
    }

    public function unpublish(User $user, BlogPost $post): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->isAdmin();
    }
}

