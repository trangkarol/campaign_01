<?php

namespace App\Repositories\Eloquent;

use Exception;
use Notification;
use App\Models\Event;
use App\Models\Media;
use App\Models\Activity;
use App\Notifications\CreateEvent;
use App\Traits\Common\UploadableTrait;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\EventInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventRepository extends BaseRepository implements EventInterface
{
    use UploadableTrait;

    public function model()
    {
        return Event::class;
    }

    public function delete($events)
    {
        if (!empty($events)) {
            $events->each(function ($event) {
                $event->goals()->delete();
                $event->donations()->delete();
                $event->settings()->delete();
                $event->media()->delete();
                $event->likes()->delete();
                $event->activities()->delete();
                $event->comments()->delete();
            });

            return $events->delete();
        }
    }

    public function deleteFromEvent($event)
    {
        if (!empty($event)) {
            $event->goals()->delete();
            $event->donations()->delete();
            $event->settings()->delete();
            $event->media()->delete();
            $event->likes()->delete();
            $event->activities()->delete();
            $event->comments()->delete();

            return $event->delete();
        }

        return false;
    }

    public function create($data)
    {
        $donation = $data['donations'];
        $dataMedias = $this->createDataMedias($data['other']['files']);
        $event = $this->model->create($data['data_event']);

        if (!$event) {
            throw new NotFoundException('Have error when create event');
        }

        $event->activities()->create([
            'user_id' => $data['data_event']['user_id'],
            'name' => Activity::CREATE,
        ]);

        $createSettings = $event->settings()->createMany($data['other']['settings']);

        if (!$createSettings) {
            throw new NotFoundException('Have error when create settings');
        }

        if ($dataMedias) {
            $createMedias = $event->media()->createMany($dataMedias);
        }

        if ($donation) {
            $goals = $event->goals()->createMany($donation);

            if (!$goals) {
                throw new NotFoundException('Have error when create model');
            }
        }

        $listReceiver = $data['campaign']->activeUsers->all();
        Notification::send($listReceiver, new CreateEvent($data['data_event']['user_id'], $event->id));

        return [
            'listReceiver' => $listReceiver,
            'modelName' => CreateEvent::class,
            'event' => $event,
        ];
    }

    private function createDataMedias($data)
    {
        $result = [];

        foreach ($data as $value) {
            $result[] = [
                'type' => Media::IMAGE,
                'url_file' => $value,
            ];
        }

        return $result;
    }

    public function update($event, $inputs)
    {
        $goals = $event->goals;

        if (count($inputs['goalAdds'])) {
            $event->goals()->createMany($inputs['goalAdds']);
        }

        if (count($inputs['mediaDels'])) {
            $media = $event->media()->whereIn('media.id', $inputs['mediaDels']);
            $fileDelete = $media->pluck('url_file');
            $media->forceDelete();
        }

        if (count($inputs['files'])) {
            $dataMedias = $this->createDataMedias($inputs['files']);
            $event->media()->createMany($dataMedias);
        }

        if (count($inputs['settings'])) {
            $event->settings()->forceDelete();
            $event->settings()->createMany($inputs['settings']);
        }

        $inputs = array_except($inputs, [
            'files',
            'mediaDels',
            'goalAdds',
            'settings',
        ]);
        parent::update($event->id, $inputs);

        if (!empty($fileDelete)) {
            foreach ($fileDelete as $value) {
                $this->destroyFile($value, 'image');
            }
        }

        return true;
    }

    public function updateSettings($event, $listSetting)
    {
        $settings = $event->settings->pluck('id')->toArray();

        if (array_keys($listSetting) != $settings) {
            throw new UnknowException('Error: Key does not match.');
        }

        foreach ($listSetting as $key => $setting) {
            $event->settings->find($key)->update([
                'value' => $setting,
            ]);
        }

        return true;
    }

    public function getEvent($event, $userId)
    {
        $inforEvent = $event->withTrashed()
            ->with(['media' => function ($query) {
                $query->withTrashed();
            }, 'user'])
            ->getLikes()
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.paginate_event'));
        $events = $inforEvent->each(function ($item) {
            $item->load(['comments' => function ($query) {
                $query->withTrashed()
                    ->getLikes()
                    ->where('parent_id', config('settings.comment_parent'))
                    ->orderBy('created_at', 'desc')
                    ->paginate(config('settings.paginate_comment'), ['*'], 1);
            }]);
        });

        return [
            'inforPage' => $inforEvent,
            'data' => $events,
        ];
    }

    public function createOrDeleteLike($event, $userId)
    {
        if (!is_numeric($userId) || !$event) {
            return false;
        }

        if ($event->likes->where('user_id', $userId)->isEmpty()) {
            return $this->createByRelationship('likes', [
                'model' => $event,
                'attribute' => ['user_id' => $userId],
            ]);
        }

        return $event->likes()->where('user_id', $userId)->first()->forceDelete();
    }

    public function openFromEvent($event)
    {
        if (!empty($event)) {
            $event->goals()->restore();
            $event->donations()->restore();
            $event->settings()->restore();
            $event->media()->restore();
            $event->likes()->restore();
            $event->activities()->restore();
            $event->comments()->restore();

            return $event->restore();
        }

        return false;
    }

    public function openFromCampaign($events)
    {
        if (!empty($events)) {
            $events->restore();

            $events->each(function ($event) {
                $event->goals()->restore();
                $event->donations()->restore();
                $event->settings()->restore();
                $event->media()->restore();
                $event->likes()->restore();
                $event->activities()->restore();
                $event->comments()->restore();
            });

            return true;
        }

        return false;
    }

    public function getDetailEvent($id)
    {
        return $this->where('id', $id)
            ->getLikes('getLikes')
            ->getComments('getComments')
            ->withTrashed()
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                },
                'settings' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->get();
    }

    public function getEventIds($campaignPublic)
    {
        return $this->withTrashed()->whereIn('campaign_id', $campaignPublic)->lists('id')->all();
    }

    public function listDonations(Event $event, $params)
    {
        $query = $event->donations();

        if ($params['searchKey']) {
            $query->search($params['searchKey'], null, true);
        }

        unset($params['searchKey']);

        foreach ($params as $key => $value) {
            if ($value) {
                $query->where($key, $value);
            }
        }

        return $query->with(['user', 'goal.donationType.quality'])->paginate();
    }
}
