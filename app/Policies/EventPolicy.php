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
            || $event->campaign->getUserByRole([Role::ROLE_OWNER, Role::ROLE_MODERATOR])->get()->contains($user);
    }

    public function comment(User $user, Event $event)
    {
        return $user->can('comment', $event->campaign);
    }

    public function like(User $user, Event $event)
    {
        return $user->can('like', $event->campaign);
    }

    public function member(User $user, Event $event)
    {
        return $user->can('member', $event->campaign);
    }
}
