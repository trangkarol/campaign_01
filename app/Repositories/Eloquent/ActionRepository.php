<?php

namespace App\Repositories\Eloquent;

use Exception;
use Notification;
use Carbon\Carbon;
use App\Models\Media;
use App\Models\Action;
use App\Models\Activity;
use App\Notifications\CreateAction;
use App\Traits\Common\UploadableTrait;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\ActionInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActionRepository extends BaseRepository implements ActionInterface
{
    use UploadableTrait;

    public function model()
    {
        return Action::class;
    }

    public function showAction($action, $userId)
    {
        $data = [];
        $checkLikeAction = $actions = $this->model->withTrashed()
            ->where('id', $action->id)
            ->getLikes()
            ->getComments()
            ->with(['user', 'media' => function ($query) {
                $query->withTrashed();
            }]);

        $data['list_action'] = $actions->first();
        $data['checkLikeAction'] = $this->checkLike($checkLikeAction, $userId);

        return $data;
    }

    public function update($action, $inputs)
    {
        if (!empty($inputs['upload'])) {
            $result = $this->createDataMedias($inputs['upload']);
            $action->media()->createMany($result);
        }

        return parent::update($action->id, $inputs['data_action']);
    }

    public function createAction($inputs, $event, $user)
    {
        $action = parent::create($inputs['data_action']);
        $action->activities()->create([
            'user_id' => $inputs['data_action']['user_id'],
            'name' => Activity::CREATE,
        ]);

        if (!is_null($inputs['upload'])) {
            $media = $this->createDataMedias($inputs['upload']);
            $action->media()->createMany($media);
        }

        Notification::send($event->user()->first(), new CreateAction($user, $event));

        return [
            'action' => $this->getOneAction($action->id),
            'modelName' => CreateAction::class,
        ];
    }

    public function getActionPaginate($action, $userId)
    {
        $data['list_action'] = $action
            ->getLikes()
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->orderBy('created_at', 'DESC')
            ->paginate(config('settings.actions.paginate_in_event'));

        $data['checkLikeAction'] = $this->checkLike($action, $userId);

        return $data;
    }

    public function searchAction($eventId, $key, $userId)
    {
        $actions = $this->model
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->getLikes()
            ->where('event_id', $eventId)
            ->withTrashed()
            ->search($key, null, true);

        $data['list_action'] = $actions
            ->orderBy('created_at', 'DESC')
            ->paginate(config('settings.actions.paginate_in_event'));
        $data['checkLikeAction'] = $this->checkLike($actions, $userId);

        return $data;
    }

    public function getActionPhotos($eventIds, $userId)
    {
        $actions = $this->model
            ->withTrashed()
            ->whereIn('event_id', $eventIds)
            ->getLikes()
            ->getComments()
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->whereHas('media', function ($query) {
                $query->withTrashed()->where('type', Media::IMAGE)
                    ->orderBy('created_at', 'desc');
            });

        $dataAction['list_action'] = $actions->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->paginate(config('settings.pagination.action'));

        $dataAction['checkLikeAction'] = $this->checkLike($actions, $userId);

        return $dataAction;
    }

    public function deleteAction($eventIds)
    {
        if (!is_null($eventIds)) {
            $actions = $this->model->whereIn('event_id', $eventIds);
            $actions->get()->each(function ($action) {
                $action->comments()->delete();
                $action->likes()->delete();
                $action->media()->delete();
                $action->activities()->delete();
            });

            return $actions->delete();
        }

        return false;
    }

    public function openAction($eventIds)
    {
        if (!is_null($eventIds)) {
            $actions = $this->model->whereIn('event_id', $eventIds);
            $actions->restore();
            $actions->each(function ($action) {
                $action->comments()->restore();
                $action->likes()->restore();
                $action->media()->restore();
                $action->activities()->restore();
            });

            return true;
        }

        return false;
    }

    public function deleteFromEvent($actions)
    {
        $actionIds = $actions->pluck('id');
        $actions = $this->model->whereIn('id', $actionIds);
        \DB::table('media')
            ->whereIn('mediable_id', $actionIds)
            ->where('mediable_type', Action::class)
            ->update(['deleted_at' => Carbon::Now()]);
        \DB::table('comments')
            ->whereIn('commentable_id', $actionIds)
            ->where('commentable_type', \App\Models\Action::class)
            ->update(['deleted_at' => Carbon::Now()]);
        \DB::table('likes')
            ->whereIn('likeable_id', $actionIds)
            ->where('likeable_type', \App\Models\Action::class)
            ->update(['deleted_at' => Carbon::Now()]);
        \DB::table('activities')
            ->whereIn('activitiable_id', $actionIds)
            ->where('activitiable_type', \App\Models\Action::class)
            ->update(['deleted_at' => Carbon::Now()]);

        return $actions->delete();
    }

    public function openFromEvent($event)
    {
        $actionIds = $event->actions()->onlyTrashed()->pluck('id');
        \DB::table('media')
            ->whereIn('mediable_id', $actionIds)
            ->where('mediable_type', Action::class)
            ->update(['deleted_at' => null]);
        \DB::table('comments')
            ->whereIn('commentable_id', $actionIds)
            ->where('commentable_type', \App\Models\Action::class)
            ->update(['deleted_at' => null]);
        \DB::table('likes')
            ->whereIn('likeable_id', $actionIds)
            ->where('likeable_type', \App\Models\Action::class)
            ->update(['deleted_at' => null]);
        \DB::table('activities')
            ->whereIn('activitiable_id', $actionIds)
            ->where('activitiable_type', \App\Models\Action::class)
            ->update(['deleted_at' => null]);

        return $event->actions()->restore();
    }

    public function createFromExpense($data, $goal)
    {
        $newData = $this->initData($data, $goal);
        $action = parent::create($newData);

        return $this->getOneAction($action->id);
    }

    public function updateFromExpense($data, $goal)
    {
        $dataUpdate = $this->initData($data, $goal);
        $actionId = $this->where('expense_id', $data['expense_id'])->first()->id;

        return parent::update($actionId, $dataUpdate);
    }

    public function deleteFromExpense($expenseId)
    {
        $action = $this->where('expense_id', $expenseId)->first();

        if (!$action) {
            throw new NotFoundException('Not found action with expense_id:' . $expenseId, NOT_FOUND);
        }

        return $action->forceDelete();
    }

    public function forceDelete($action)
    {
        $action->comments()->forceDelete();
        $action->likes()->forceDelete();
        $action->activities()->forceDelete();

        return $action->forceDelete();
    }

    private function initData($data, $goal)
    {
        $data['description'] = $data['reason'];
        $caption = [
            'expenseTime' => $data['time'],
            'cost' => $data['cost'],
            'typeName' => $goal->donationType->name,
            'nameQuality' => $goal->donationType->quality->name,
        ];
        $data['caption'] = json_encode($caption);

        return array_only($data, [
            'caption',
            'description',
            'event_id',
            'user_id',
            'expense_id',
        ]);
    }

    public function getOneAction($id)
    {
        $data['list_action'] = $this->where('id', $id)
            ->getLikes('getLikes')
            ->getComments('getComments')
            ->withTrashed()
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->first();

        return $data;
    }

    public function actionIds($eventId)
    {
        $data['list_action'] = $this->where('id', $id)
            ->getLikes('getLikes')
            ->getComments('getComments')
            ->withTrashed()
            ->with([
                'user',
                'media' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->first();

        return $data;
    }

    public function getActionIds($eventIds)
    {
        return $this->withTrashed()->whereIn('event_id', $eventIds)->lists('id')->all();
    }
}
