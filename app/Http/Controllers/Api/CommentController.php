<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommentRequest;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\CommentInterface;
use Illuminate\Http\Request;

class CommentController extends ApiController
{
    protected $eventRepository;
    protected $actionRepository;
    protected $commentRepository;

    public function __construct(
        EventInterface $eventRepository,
        ActionInterface $actionRepository,
        CommentInterface $commentRepository
    ) {
        parent::__construct();
        $this->eventRepository = $eventRepository;
        $this->actionRepository = $actionRepository;
        $this->commentRepository = $commentRepository;
    }

    public function createComment($modelId, $parentId, $flag, CommentRequest $request)
    {
        if (!$modelId) {
            throw new UnknowException('Event_id is null');
        }

        $commentClass = new \ReflectionClass($this->commentRepository);
        $model = app($commentClass->getNamespaceName() . '\\' . ucfirst($flag . 'Interface'))->findOrFail($modelId);

        $data['parent_id'] = $parentId;
        $data['content'] = $request->content;
        $data['user_id'] = $this->user->id;

        if ($this->user->cant('comment', $model)) {
            throw new UnknowException('Permission error: User can not create comment in this post.');
        }

        return $this->doAction(function () use ($data, $model) {
            $this->compacts['createComment'] = $this->commentRepository->createComment($data, $model);

            if ($this->compacts['createComment']->parent_id == config('settings.comment_parent')) {
                $numberComment = $model->number_of_comments + 1;
                $model->update([
                    'number_of_comments' => $numberComment
                ]);
            } else {
                $comment = $this->commentRepository->findOrFail($this->compacts['createComment']->parent_id);
                $numberComment = $comment->number_of_comments + 1;
                $comment->update([
                    'number_of_comments' => $numberComment
                ]);
            }

            $this->compacts['numberComment'] = $numberComment;
        });
    }

    public function updateComment($id, $flag, CommentRequest $request)
    {
        $data = $request->only('content');
        $comment = $this->commentRepository->findOrFail($id);
        $modelId = $comment->commentable_id;

        $commentClass = new \ReflectionClass($this->commentRepository);
        $model = app($commentClass->getNamespaceName() . '\\' . ucfirst($flag . 'Interface'))->findOrFail($comment->commentable_id);

        if ($this->user->cant('update', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($id, $data, $comment, $model) {
            $this->compacts['updateComment'] = $this->commentRepository->updateComment($data, $comment, $this->user);

            if ($comment->parent_id == config('settings.comment_parent')) {
                $numberComment = $model->number_of_comments;
            } else {
                $numberComment = $comment->number_of_comments;
            }

            $this->compacts['numberComment'] = $numberComment;
        });
    }

    public function destroy($id, Request $request)
    {
        $comment = $this->commentRepository->findOrFail($id);
        $data = $request->only('modelId', 'flag');

        $commentClass = new \ReflectionClass($this->commentRepository);
        $model = app($commentClass->getNamespaceName() . '\\' . ucfirst($data['flag'] . 'Interface'))->findOrFail($data['modelId']);

        if ($this->user->cant('delete', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($comment, $model) {
            $this->compacts['deleteComment'] = $this->commentRepository->deleteComment($comment, $this->user);

            if ($comment->parent_id == 0) {
                $numberComment = $model->number_of_comments - 1;
                $model->update([
                    'number_of_comments' => $numberComment
                ]);
            } else {
                $parentComment = $this->commentRepository->findOrFail($comment->parent_id);
                $numberComment = $parentComment->number_of_comments - 1;
                $parentComment->update([
                    'number_of_comments' => $numberComment
                ]);
            }

            $this->compacts['numberComment'] = $numberComment;
        });
    }

    public function show($modelId)
    {
        return $this->getData(function () use ($modelId) {
            $this->compacts['loadMore'] = $this->commentRepository->getComment($modelId);
        });
    }

    public function getSubComment($parentId)
    {
        return $this->getData(function () use ($parentId) {
            $this->compacts['subComment'] = $this->commentRepository->getSubComment($parentId);
        });
    }
}
