<?php

namespace App\Repositories\Eloquent;

use Notification;
use App\Models\Comment;
use App\Models\Activity;
use App\Notifications\UserComment;
use App\Exceptions\Api\UnknowException;
use App\Repositories\Contracts\CommentInterface;
use Illuminate\Support\Facades\Event;

class CommentRepository extends BaseRepository implements CommentInterface
{
    public function model()
    {
        return Comment::class;
    }

    public function createComment($data, $model, $flag)
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

        if ($data['parent_id']) {
            $commentParent = $this->findOrFail($data['parent_id']);
            $numberComment = $commentParent->number_of_comments + 1;
            $receiver = $commentParent->user()->first();
            $commentParent->update([
                'number_of_comments' => $numberComment,
            ]);
        } else {
            $numberComment = $model->number_of_comments + 1;
            $receiver = ($flag == config('settings.type_notification.campaign'))
                ? $model->owner()->first()
                : $model->user()->first();
            $model->update([
                'number_of_comments' => $numberComment,
            ]);
        }

        if ($receiver->id != $data['user_id']) {
            $send = true;
            Notification::send($receiver, new UserComment($data['user_id'], $comment->id));
        }

        return [
            'comment' => $comment->load('commentable'),
            'numberComment' => $numberComment,
            'receiver' => $receiver->id,
            'type' => UserComment::class,
        ];
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

    public function getComment($modelId, $model)
    {
        return $this->withTrashed()
            ->with('user')
            ->getLikes()
            ->where('parent_id', config('settings.comment_parent'))
            ->where('commentable_id', $modelId)
            ->where('commentable_type', $model)
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.paginate_comment'));
    }

    public function getSubComment($id)
    {
        $comment = $this->withTrashed()->findOrFail($id);

        return $comment->subComment()->withTrashed()
           ->with('user')
           ->getLikes()
           ->orderBy('created_at', 'desc')
           ->paginate(config('settings.paginate_comment'));
    }

    public function deleteComment($comment, $user)
    {
        $comment->activities()->forceDelete();
        $comment->likes()->forceDelete();
        $comment->subComment()->forceDelete();
        $comment->forceDelete();

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
