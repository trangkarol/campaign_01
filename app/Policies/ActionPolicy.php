<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Action;
use App\Models\Role;
use App\Models\Campaign;

class ActionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the action.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Action  $action
     * @return mixed
     */
    public function view(User $user, Action $action)
    {
        return $user->can('view', $action->event);
    }

    /**
     * Determine whether the user can manage the action.(update/delete)
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Action  $action
     * @return mixed
     */
    public function manage(User $user, Action $action)
    {
        return $user->id === $action->user_id
            || $user->can('manage', $action->event);
    }

    public function comment(User $user, Action $action)
    {
        $roleBlockId = Role::where('name', Role::ROLE_BLOCKED)->first()->pluck('id');

        $userInCampaign = $action->event->campaign->users()
            ->wherePivot('status', Campaign::APPROVED)
            ->wherePivot('role_id', '<>', $roleBlockId)
            ->pluck('user_id')
            ->all();

        return in_array($user->id, $userInCampaign);
    }

    public function like(User $user, Action $action)
    {
        $roleBlockId = Role::where('name', Role::ROLE_BLOCKED)->first()->pluck('id');

        $userInCampaign = $action->event->campaign->users()
            ->wherePivot('status', Campaign::APPROVED)
            ->wherePivot('role_id', '<>', $roleBlockId)
            ->pluck('user_id')
            ->all();

        return in_array($user->id, $userInCampaign);
    }
}
