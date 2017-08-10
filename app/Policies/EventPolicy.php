<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\Role;
use App\Models\Campaign;

class EventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return mixed
     */
    public function view(User $user, Event $event)
    {
        return $user->can('view', $event->campaign);
    }

    /**
     * Determine whether the user can manage the event. (delete/update)
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return mixed
     */
    public function manage(User $user, Event $event)
    {
        return $user->id === $event->user_id
            || $user->can('manage', $event->campaign);
    }

    public function comment(User $user, Event $event)
    {
        $roleBlockId = Role::where('name', Role::ROLE_BLOCKED)->first()->pluck('id');

        $userInCampaign = $event->campaign->users()
            ->wherePivot('status', Campaign::APPROVED)
            ->wherePivot('role_id', '<>', $roleBlockId)
            ->pluck('user_id')
            ->all();

        return in_array($user->id, $userInCampaign);
    }

    public function like(User $user, Event $event)
    {
        $roleBlockId = Role::where('name', Role::ROLE_BLOCKED)->first()->pluck('id');

        $userInCampaign = $event->campaign->users()
            ->wherePivot('status', Campaign::APPROVED)
            ->wherePivot('role_id', '<>', $roleBlockId)
            ->pluck('user_id')
            ->all();

        return in_array($user->id, $userInCampaign);
    }
}
