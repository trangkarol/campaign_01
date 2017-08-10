<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;

class ActivityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can like the activity.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Activity  $activity
     * @return mixed
     */
    public function like(User $user, Activity $activity)
    {
        dd('dsds');
        $userActivity = User::findOrFail($activity->user_id);
        $friendIds = $userActivity->friends()->pluck('id')->all();

        return in_array($user->id, $friendIds)
            || $user->id === $activity->user_id;
    }

    /**
     * Determine whether the user can comment the activity.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Activity  $activity
     * @return mixed
     */
    public function comment(User $user, Activity $activity)
    {
        $userActivity = User::findOrFail($activity->user_id);
        $friendIds = $userActivity->friends()->pluck('id')->all();

        return in_array($user->id, $friendIds)
            || $user->id === $activity->user_id;
    }
}
