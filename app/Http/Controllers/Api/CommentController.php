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

        if ($parentId != config('settings.comment_parent')) {
             $data['parent_id'] = $parentId;
        }

        $data['content'] = $request->content;
        $data['user_id'] = $this->user->id;

        if ($flag) {
            switch ($flag) {
                case 'event':
                    $model = $this->eventRepository->findOrFail($modelId);
                    break;
                case 'action':
                    $model = $this->actionRepository->findOrFail($modelId);
                    break;
                default:
                    $model = '';
            }
        }

        if ($this->user->cant('comment', $model)) {
            throw new UnknowException('Permission error: User can not create comment in this post.');
        }

        return $this->doAction(function () use ($data, $model) {
            $this->compacts['createComment'] = $this->commentRepository->createComment($data, $model);
        });
    }

    public function update(CommentRequest $request, $id)
    {
        $data = $request->only('content');
        $comment = $this->commentRepository->findOrFail($id);
        $modelId = $comment->commentable_id;

        if ($this->user->cant('update', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($id, $data) {
            $this->compacts['updateComment'] = $this->commentRepository->update($id, $data);
        });
    }

    public function destroy($id, Request $request)
    {
        $comment = $this->commentRepository->findOrFail($id);
        $modelId = $request->modelId;

        if ($this->user->cant('delete', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($id) {
            $this->compacts['deleteComment'] = $this->commentRepository->delete($id);
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
