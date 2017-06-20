<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommentRequest;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\CommentInterface;

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

    public function createCommentEvent($modelId, CommentRequest $request)
    {
        if (!$modelId) {
            throw new UnknowException('Event_id is null');
        }

        $data = $request->intersect('parent_id', 'content');
        $data['event_id'] = $modelId;
        $data['user_id'] = $this->user->id;

        $event = $this->eventRepository->findOrFail($data['event_id']);

        if ($this->user->cant('comment', $event)) {
            throw new UnknowException('Permission error: User can not create comment in this event.');
        }

        return $this->doAction(function () use ($data, $event) {
            $this->compacts['createComment'] = $this->commentRepository->createComment($data, $event);
        });
    }

    public function createCommentAction(CommentRequest $request)
    {
        if (!$request->action_id) {
            throw new UnknowException('Action_id is null');
        }

        $data = $request->intersect('parent_id', 'content');
        $data['user_id'] = $this->user->id;
        $action = $this->actionRepository->findOrFail($request->action_id);

        if ($this->user->cant('comment', $action)) {
            throw new UnknowException('Permission error: User can not comment for this action.');
        }

        return $this->doAction(function () use ($data, $action) {
            $this->compacts['createComment'] = $this->commentRepository->createComment($data, $action);
        });
    }

    public function update(CommentRequest $request, $id)
    {
        $data = $request->only('content');
        $comment = $this->commentRepository->findOrFail($id);

        if ($this->user->cant('update', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($id, $data) {
            $this->compacts['updateComment'] = $this->commentRepository->update($id, $data);
        });
    }

    public function destroy($id)
    {
        $comment = $this->commentRepository->findOrFail($id);

        if ($this->user->cant('delete', $comment)) {
            throw new UnknowException('Permission error: User can not edit this comment.');
        }

        return $this->doAction(function () use ($id) {
            $this->compacts['deleteComment'] = $this->commentRepository->delete($id);
        });
    }

    public function show($modelId)
    {
        // $comment = $this->commentRepository->findOrFail($id);

        // if ($this->user->cant('update', $comment)) {
        //     throw new UnknowException('Permission error: User can not edit this comment.');
        // }

        return $this->getData(function () use ($modelId) {
            $this->compacts['comment'] = $this->commentRepository->getComment($modelId);
        });
    }
}
