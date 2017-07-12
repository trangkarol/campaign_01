<?php

namespace App\Repositories\Eloquent;

use Exception;
use App\Models\Event;
use App\Models\Media;
use App\Models\Activity;
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

    public function delete($eventIds)
    {
        if (!$eventIds) {
            $events = $this->model->whereIn('id', $eventIds);
            $events->goals()->delete();
            $events->action()->delete();

            return $events->delete();
        }
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

        return true;
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

        if (isset($inputs['goalAdd'])) {
            $event->goals()->createMany($inputs['goalAdd']);
        }

        if (isset($inputs['goalUpdate']) && $goals) {
            foreach ($inputs['goalUpdate'] as $key => $value) {
                $goal = $goals->find($key);

                if (!$goal) {
                    throw new UnknowException('Error: Goal is not found.');
                }

                $goal->update($value);
            }
        }

        if (!empty($inputs['mediaDels'])) {
            foreach ($inputs['mediaDels'] as $mediaId) {
                $media = $event->media->find($mediaId);

                if (!$media) {
                    throw new UnknowException('Error: Image is not found');
                }

                $this->destroyFile($media->url_file, 'image');
                $media->forceDelete();
            }
        }

        if (!empty($inputs['mediaAdds'])) {
            foreach ($inputs['mediaAdds'] as $mediaId) {
                $urlFile = $this->uploadFile($mediaId, 'event');
                $event->media()->create([
                    'url_file' => $urlFile,
                    'type' => Media::IMAGE,
                ]);
            }
        }

        if (!empty($inputs['goalDels'])) {
            $event->goals()->delete($inputs['goalDels']);
        }

        $inputs = array_except($inputs, ['mediaAdds', 'mediaDels']);
        parent::update($event->id, $inputs);

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

    public function getEventExist($id)
    {
        $event = $this->find($id);

        if (!$event) {
            throw new UnknowException('Error: Event is not found.');
        }

        return $event;
    }

    public function getEvent($event)
    {
        return $event->with('actions', 'likes.user', 'donations', 'media', 'user')->orderBy('created_at', 'desc')
           ->paginate(config('settings.paginate_default'));
        // return $event->with(['actions', 'likes.user', 'donations', 'media', 'user', 'comments' => function ($query) {
        //     $query->with(['user', 'likes', 'subComment' => function ($subQuery) {
        //         $subQuery->with('user', 'likes');
        //     }])->where('parent_id', config('settings.comment_parent'))
        //        ->paginate(config('settings.paginate_comment'));
        // }])->orderBy('created_at', 'desc')
        //    ->paginate(config('settings.paginate_default'));
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
}
