<?php

namespace App\Repositories\Eloquent;

use Exception;
use App\Models\Activity;
use App\Repositories\Contracts\ActivityInterface;

class ActivityRepository extends BaseRepository implements ActivityInterface
{
    public function model()
    {
        return Activity::class;
    }
}
