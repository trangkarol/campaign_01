<?php

namespace App\Repositories\Contracts;

interface LikeInterface extends RepositoryInterface
{
    public function likeOrUnlike($model, $userId);

    public function getListMemberLiked($model);
}
