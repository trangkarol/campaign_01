<?php

namespace App\Repositories\Eloquent;

use Exception;
use App\Models\Activity;
use App\Models\Campaign;
use App\Models\Event;
use App\Exceptions\Api\UnknowException;
use App\Repositories\Contracts\ActivityInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityRepository extends BaseRepository implements ActivityInterface
{
    public function model()
    {
        return Activity::class;
    }

    public function getNewsFeed($campaignIds, $eventIds)
    {
        $this->setGuard('api');
        $friendIds = $this->user->friends()->pluck('id')->all();
        $friendIds[] = $this->user->id;
        $infoPaginate = $this->withTrashed()->whereIn('activitiable_type', [
            Campaign::class,
            Event::class,
        ])
        ->where('name', Activity::CREATE)
        ->where(function ($query) use ($campaignIds, $eventIds) {
            $query->where(function ($query) use ($eventIds) {
                    $query->whereIn('activitiable_id', $eventIds)
                        ->where('activitiable_type', Event::class);
                })
                ->orWhere(function ($sub) use ($campaignIds) {
                    $sub->where('activitiable_type', Campaign::class)
                        ->whereIn('activitiable_id', $campaignIds);
                });
        })
        ->whereIn('user_id', $friendIds)
        ->with('user')
        ->orderBy('created_at', 'DESC')
        ->paginate(config('settings.pagination.homepage'));

        $listActivity = $infoPaginate->each(function ($item) {
            if ($item->activitiable_type == Campaign::class) {
                $item->load(['activitiable' => function ($query) {
                    $query->withTrashed()->with(['media' => function ($subQuery) {
                        $subQuery->withTrashed();
                    }]);
                }]);
            } else {
                $item->load(['activitiable' => function ($query) {
                    $query->withTrashed()->with(['campaign' => function ($subQuery1) {
                        $subQuery1->withTrashed();
                    }, 'media' => function ($subQuery2) {
                        $subQuery2->withTrashed();
                    }]);
                }]);
            }
        });

        return [
            'infoPaginate' => $infoPaginate,
            'data' => $listActivity,
        ];
    }
}
