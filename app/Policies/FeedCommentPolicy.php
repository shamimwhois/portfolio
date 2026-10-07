<?php

namespace App\Policies;

use App\Models\FeedComment;
use App\Models\User;

class FeedCommentPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, FeedComment $comment): bool
    {
        return $user->isAdmin() || $user->id === $comment->user_id;
    }
}
