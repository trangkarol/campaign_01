<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy extends BasePolicy
{
    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id
            || $user->can('manager', $comment->commentable);
    }

    public function like(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id;
    }
}
