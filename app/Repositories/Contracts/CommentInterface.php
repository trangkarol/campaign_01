<?php

namespace App\Repositories\Contracts;

interface CommentInterface extends RepositoryInterface
{
    public function createComment($data, $model);

     public function getComment($modelId, $model, $idCurrent);

    public function updateComment($data, $comment, $user);

    public function deleteComment($comment, $user);
}
