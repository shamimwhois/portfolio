<?php

namespace App\Policies;

use App\Models\FeedPost;
use App\Models\User;

class FeedPostPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, FeedPost $post): bool
    {
        return $user->isAdmin() || $user->id === $post->user_id;
    }

    public function delete(User $user, FeedPost $post): bool
    {
        return $user->isAdmin() || $user->id === $post->user_id;
    }
}
