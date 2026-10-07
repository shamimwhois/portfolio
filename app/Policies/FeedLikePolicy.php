<?php

namespace App\Policies;

use App\Models\User;

class FeedLikePolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }
}
