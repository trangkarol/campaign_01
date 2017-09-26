<?php

namespace App\Traits\Eloquent;

trait CommentLike
{
    public function scopeGetComments($query)
    {
        return $query->with(['comments' => function ($query) {
            $query
                ->withTrashed()
                ->getLikes()
                ->where('parent_id', config('settings.comment_parent'))
                ->orderBy('created_at', 'desc')
                ->paginate(config('settings.paginate_comment'), ['*'], 1);
        }]);
    }

    public function scopeGetLikes($query)
    {
        return $query->with(['likes' => function ($query) {
            $query->withTrashed()->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(config('settings.paginate_default'), ['*'], 1);
        }]);
    }
}
