<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Http\Requests\CommentRequest;
use App\Exceptions\Api\UnknowException;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Controllers\AbstractController;
use App\Repositories\Contracts\DonationTypeInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\QualityInterface;
use App\Repositories\Contracts\CampaignInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\GoalInterface;
use App\Repositories\Contracts\ExpenseInterface;
use LRedis;
use App\Models\Event;
use App\Models\Activity;

class EventController extends ApiController
{
    protected $eventRepository;
    protected $qualityRepository;
    protected $campaignRepository;
    protected $donationTypeRepository;
    protected $actionRepository;
    protected $goalRepository;
    protected $expenseRepository;
    private $redis;

    public function __construct(
        EventInterface $eventRepository,
        QualityInterface $qualityRepository,
        CampaignInterface $campaignRepository,
        DonationTypeInterface $donationTypeRepository,
        ActionInterface $actionRepository,
        GoalInterface $goalRepository,
        ExpenseInterface $expenseRepository
    ) {
        parent::__construct();
        $this->qualityRepository = $qualityRepository;
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->donationTypeRepository = $donationTypeRepository;
        $this->actionRepository = $actionRepository;
        $this->goalRepository = $goalRepository;
        $this->expenseRepository = $expenseRepository;
    }

    public function create(EventRequest $request)
    {
        $input['data_event'] = $request->intersect('campaign_id', 'title', 'description', 'longitude', 'latitude', 'address');
        $input['data_event']['user_id'] = $this->user->id;
        $input['other'] = $request->only('settings', 'files');
        $input['donations'] = $this->qualityRepository->getOrCreate($request->get('donations'));
        $campaign = $this->campaignRepository->find($input['data_event']['campaign_id']);

        return $this->doAction(function () use ($input, $campaign) {
            if ($this->user->cant('createEvent', $campaign)) {
                throw new UnknowException('Have error when create event');
            }

            $this->compacts['event'] = $this->eventRepository->create($input);
            $this->redis = LRedis::connection();
            $feature = $campaign
                ->settings()
                ->where('key', config('settings.campaigns.status'))
                ->first();
            $this->redis->publish('createEvent', json_encode([
                'info' => $this->compacts['event']->load('campaign', 'media'),
                'hashtag' => $campaign->hashtag,
                'type' => Event::class,
                'user' => $this->user,
                'name' => Activity::CREATE,
                'feature' => $feature
                    ? $feature->value
                    : config('settings.value_of_settings.status.private'),
            ]));
        });
    }

    public function edit($id)
    {
        $event = $this->eventRepository->findOrFail($id);

        if ($this->user->cannot('manage', $event)) {
            throw new UnknowException('Permission error: User can not edit this event.');
        }

        return $this->getData(function () use ($event) {
            $this->compacts['event'] = $this->eventRepository->getDetailEvent($event->id);
            $this->compacts['goals'] = $this->goalRepository->getGoalFromEvent($event);
        });
    }

    public function update(EventRequest $request, $id)
    {
        $data = $request->only(
            'title',
            'description',
            'longitude',
            'latitude',
            'settings',
            'address',
            'files',
            'mediaDels',
            'goalDels',
            'goalUpdates',
            'goalAdds'
        );

        $event = $this->eventRepository->findOrFail($id);

        if ($this->user->cannot('manage', $event)) {
            throw new UnknowException('Permission error: User can not edit this event.');
        }

        return $this->doAction(function () use ($event, $data) {
            if (count($data['goalAdds'])) {
                $data['goalAdds'] = $this->qualityRepository->getOrCreate($data['goalAdds']);
            }

            if (count($data['goalUpdates'])) {
                $this->goalRepository->updateManyRow($data['goalUpdates']);
            }

            $data = array_except($data, ['goalUpdates']);
            $this->compacts['event'] = $this->eventRepository->update($event, $data);
        });
    }

    public function updateSetting(Request $request, $id)
    {
        if (!is_array($request->setting)) {
            throw new UnknowException('Error: Invalid parameter.');
        }

        $event = $this->eventRepository->findOrFail($id);

        return $this->doAction(function () use ($event, $request) {
            $this->compacts['event'] = $this->eventRepository->updateSettings($event, $request->setting);
        });
    }

    public function getTypeQuality()
    {
        return $this->doAction(function () {
            $this->compacts['qualitys'] = $this->qualityRepository->distinct()->select('name')->get();
            $this->compacts['types'] = $this->donationTypeRepository->distinct()->select('name')->get();
        });
    }

    public function show(Request $request, $id)
    {
        $event = $this->eventRepository->withTrashed()->where('campaign_id', $request->campaignId)->findOrFail($id);

        if ($this->user->cannot('view', $event)) {
            throw new UnknowException('Permission error: User can not view this event.');
        }

        return $this->getData(function () use ($event) {
            $this->compacts['event'] = $this->eventRepository->getDetailEvent($event->id);

            $this->compacts['actions'] = $this->actionRepository
                ->getActionPaginate($event->actions()->withTrashed(), $this->user->id);

            $this->compacts['goals'] = $event
                ->goals()
                ->withTrashed()
                ->select('id', 'donation_type_id', 'goal')
                ->with([
                    'donations' => function ($query) {
                        return $query->with('user')->latest();
                    },
                    'donationType.quality' => function ($query) {
                        $query->withTrashed();
                    },
                ])
                ->get();
            $this->compacts['manage'] = $this->user->can('manage', $event);
            $this->compacts['member'] = $this->user->can('member', $event);
            $this->compacts['checkLikeEvent'] = $this->eventRepository->checkLike($event, $this->user->id);
        });
    }

    public function destroy($id)
    {
        $event = $this->eventRepository->findOrFail($id);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Permission error: User can not delete this event.');
        }

        $actions = $event->actions();
        $expenses = $event->expenses();

        return $this->doAction(function () use ($event, $actions, $expenses) {
            $this->actionRepository->deleteFromEvent($actions);
            $this->expenseRepository->deleteFromEvent($expenses);
            $this->compacts['deleteEvent'] = $this->eventRepository->deleteFromEvent($event);
        });
    }

    public function getInfoEvent($id)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $event)) {
            throw new UnknowException('Permission denied');
        }

        return $this->getData(function () use ($event) {
            $this->compacts['event'] = $event->load([
                'settings' => function ($query) {
                    $query->withTrashed();
                },
            ]);
            $this->compacts['countActions'] = $event->actions()->withTrashed()->count();
        });
    }

    public function openEvent($id)
    {
        $event = $this->eventRepository->onlyTrashed()->findOrFail($id);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Permission error: User can not delete this event.');
        }

        return $this->doAction(function () use ($id, $event) {
            $this->actionRepository->openFromEvent($event);
            $this->expenseRepository->openFromEvent($event);
            $this->compacts['openEvent'] = $this->eventRepository->openFromEvent($event);
        });
    }
}
