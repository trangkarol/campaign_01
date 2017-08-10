<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommentRequest;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\LikeInterface;
use Illuminate\Http\Request;

class LikeController extends ApiController
{
    protected $likeRepository;

    public function __construct(LikeInterface $likeRepository)
    {
        parent::__construct($likeRepository);
        $this->likeRepository = $likeRepository;
    }

    public function like($modelId, $flag)
    {
        // dd($modelId, $flag);
        $likeClass = new \ReflectionClass($this->likeRepository);
        $model = app($likeClass->getNamespaceName() . '\\' . ucfirst($flag . 'Repository'))->findOrFail($modelId);

        if (!$modelId) {
            throw new UnknowException('modelId is null');
        }

        if ($this->user->cant('like', $model)) {
            throw new UnknowException('Permission error: User can not like in this post.');
        }

        return $this->doAction(function () use ($model) {
            $this->compacts['like'] = $this->likeRepository->likeOrUnlike($model, $this->user);
            // update number of likes

            if (isset($this->compacts['like']->id)) {
                $numberLike = $model->number_of_likes + 1;
            } else {
                $numberLike = $model->number_of_likes - 1;
            }

            $model->update([
                'number_of_likes' => $numberLike
            ]);

            $this->compacts['numberOfLikes'] = $numberLike;
        });
    }

    public function getListMemberLiked($modelId, $flag)
    {
        $likeClass = new \ReflectionClass($this->likeRepository);
        $model = app($likeClass->getNamespaceName() . '\\' . ucfirst($flag . 'Repository'))->findOrFail($modelId);

        if ($this->user->cant('like', $model)) {
            throw new UnknowException('Permission error: User can not see this members liked in this post.');
        }

        return $this->getData(function () use ($model) {
            $this->compacts['list_member'] = $this->likeRepository->getListMemberLiked($model);
        });
    }
}
