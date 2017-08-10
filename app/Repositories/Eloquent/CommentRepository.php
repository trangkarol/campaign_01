<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Models\Activity;
use App\Exceptions\Api\UnknowException;
use App\Repositories\Contracts\CommentInterface;
use Illuminate\Support\Facades\Event;

class CommentRepository extends BaseRepository implements CommentInterface
{
    public function model()
    {
        return Comment::class;
    }

    public function createComment($data, $model)
    {
        if (empty($data) || empty($model)) {
            throw new UnknowException('Data is null');
        }

        $comment = $model->comments()->create($data);

        Event::fire('add.activity', [
            [
                'model' => $comment,
                'user_id' => $data['user_id'],
                'action' => Activity::CREATE
            ]
        ]);

        return $comment;
    }

    public function updateComment($data, $comment, $user)
    {
        $comment->update($data);

        Event::fire('add.activity', [
            [
                'model' => $comment,
                'user_id' => $user->id,
                'action' => Activity::UPDATE
            ]
        ]);

        return $comment;
    }

    public function getComment($modelId)
    {
        return $this->with(['user', 'likes.user',
            'subComment' => function ($subQuery) {
                $subQuery->with('user', 'likes.user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(config('settings.paginate_comment'), ['*'], 1);
            }])
            ->where('parent_id', config('settings.comment_parent'))
            ->where('commentable_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.paginate_comment'));
    }

    public function getSubComment($id)
    {
        $comment = $this->findOrFail($id);

        return $comment->subComment()
           ->with('user')
           ->orderBy('created_at', 'desc')
           ->paginate(config('settings.paginate_comment'));
    }

    public function deleteComment($comment, $user)
    {
        $comment->activities()->delete();
        $comment->likes()->delete();
        $comment->subComment()->delete();
        $comment->delete();

        Event::fire('add.activity', [
            [
                'model' => $comment,
                'user_id' => $user->id,
                'action' => Activity::DELETE
            ]
        ]);

        return true;
    }
}
