<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Exceptions\Api\UnknowException;
use App\Repositories\Contracts\CommentInterface;

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

        return $model->comments()->create($data);
    }

    public function getComment($modelId)
    {
        return $this->with(['likes', 'user', 'subComment' => function ($query) {
            $query->with('likes', 'user')->paginate(10);;
        }])->where('commentable_id', $modelId)->paginate(10);
    }
}
