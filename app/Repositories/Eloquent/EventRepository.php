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

        return $event;
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
        return $event->with('media', 'user')
            ->getLikes()
            ->getComments()
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.paginate_event'));
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
