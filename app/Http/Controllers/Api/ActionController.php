<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\ActionRequest;
use App\Http\Requests\CommentRequest;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\MediaInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCampaignRequest;

class ActionController extends ApiController
{
    protected $actionRepository;
    protected $mediaRepository;
    protected $eventRepository;

    public function __construct(
        ActionInterface $action,
        MediaInterface $media,
        EventInterface $eventRepository
    ) {
        parent::__construct();
        $this->actionRepository = $action;
        $this->mediaRepository = $media;
        $this->eventRepository = $eventRepository;
    }

    public function update(ActionRequest $request, $id)
    {
        $inputs['data_action'] = $request->only('caption', 'description');
        $inputs['data_action']['user_id'] = $this->user->id;
        $inputs['upload'] = $request->upload;
        $mediaIds = $request->delete;

        $action = $this->actionRepository->findOrFail($id);

        if ($this->user->cant('manage', $action)) {
            throw new UnknowException('Permission error: User can not edit this action.');
        }

        $media = $action->media->whereIn('id', $mediaIds);

        return $this->doAction(function () use ($action, $inputs, $media) {
            $this->compacts['action'] = $this->actionRepository->update($action, $inputs);
            $this->mediaRepository->deleteMedia($media);
        });
    }

    public function store(ActionRequest $request)
    {
        $data['data_action'] = $request->only(
            'caption',
            'description',
            'event_id'
        );
        $data['data_action']['user_id'] = $this->user->id;
        $data['upload'] = $request->get('files');
        $event = $this->eventRepository->findOrFail($data['data_action']['event_id']);

        if ($this->user->cant('view', $event)) {
             throw new UnknowException('Permission error: User can not create this action.', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $this->compacts['action'] = $this->actionRepository->create($data);
        });
    }

    public function listAction($eventId)
    {
        $event = $this->eventRepository->findOrFail($eventId);

        return $this->doAction(function () use ($event) {
            $this->compacts['actions'] = $this->actionRepository->getActionPaginate($event->actions());
        });
    }

    public function searchAction(Request $request, $eventId)
    {
        $key = $request->key;

        return $this->doAction(function () use ($eventId, $key) {
            $this->compacts['actions'] = $this->actionRepository->searchAction($eventId, $key);
        });
    }

    public function delete($id)
    {
        $action = $this->actionRepository->findOrFail($id);

        if ($this->user->cant('manage', $action)) {
            throw new UnknowException('Permission error: User can not delete this action.');
        }

        return $this->doAction(function () use ($action) {
            $this->compacts['actions'] = $this->actionRepository->delete($action);
        });
    }

    public function show($id)
    {
        $action = $this->actionRepository->findOrFail($id);

        if ($this->user->cant('view', $action)) {
            throw new UnknowException('Permission error: User can not see this action.');
        }

        return $this->doAction(function () use ($action) {
            $this->compacts['actions'] = $this->actionRepository->showAction($action, $this->user->id);
        });
    }
}
