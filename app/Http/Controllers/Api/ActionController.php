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
            $result = $this->actionRepository->update($action, $inputs);
            $this->mediaRepository->deleteMedia($media);
            $this->compacts['action'] = $result->load([
                'media' => function ($query) {
                    $query->withTrashed();
                },
            ]);
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

        if ($this->user->cant('comment', $event)) {
            throw new UnknowException('Permission error: User can not create action.', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data, $event) {
            $result = $this->actionRepository->createAction($data, $event, $this->user->id);
            $this->compacts['action'] = $result['action'];

            foreach($result['listReceiver'] as $receiver) {
                $this->sendNotification(
                    $receiver->id,
                    $event,
                    $result['modelName'],
                    config('settings.type_notification.event')
                );
            }
        });
    }

    public function listAction($eventId)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($eventId);

        return $this->doAction(function () use ($event) {
            $this->compacts['actions'] = $this->actionRepository
                ->getActionPaginate($event->actions()->withTrashed(), $this->user->id);
        });
    }

    public function searchAction(Request $request, $eventId)
    {
        $key = $request->key;

        return $this->doAction(function () use ($eventId, $key) {
            $this->compacts['actions'] = $this->actionRepository->searchAction($eventId, $key, $this->user->id);
        });
    }

    public function delete($id)
    {
        $action = $this->actionRepository->findOrFail($id);

        if ($this->user->cant('manage', $action)) {
            throw new UnknowException('Permission error: User can not delete this action.');
        }

        return $this->doAction(function () use ($action) {
            $this->compacts['actions'] = $this->actionRepository->forceDelete($action);
            $this->mediaRepository->deleteMedia($action->media);
        });
    }

    public function show($id)
    {
        $action = $this->actionRepository->withTrashed()->findOrFail($id);

        if ($this->user->cant('view', $action)) {
            throw new UnknowException('Permission error: User can not see this action.');
        }

        return $this->getData(function () use ($action) {
            $this->compacts['actions'] = $this->actionRepository->showAction($action, $this->user->id);
            $this->compacts['checkPermission'] = $this->user->can('comment', $action);
        });
    }
}
