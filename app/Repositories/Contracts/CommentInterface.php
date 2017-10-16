<?php

namespace App\Repositories\Contracts;

interface CommentInterface extends RepositoryInterface
{
    public function createComment($data, $model, $flag);

    public function getComment($modelId, $model);

    public function updateComment($data, $comment, $user);

    public function deleteComment($comment, $user);
}
