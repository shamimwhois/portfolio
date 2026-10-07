<?php

namespace App\Policies;

use App\Models\BlogComment;
use App\Models\User;

class BlogCommentPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, BlogComment $comment): bool
    {
        return $user->isAdmin() || $user->id === $comment->user_id;
    }
}
