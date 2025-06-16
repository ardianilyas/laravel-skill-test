<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(Post $post, User $user) {
        return $user->id === $post->user_id;
    }

    public function delete(Post $post, User $user) {
        return $user->id === $post->user_id;
    }
}
