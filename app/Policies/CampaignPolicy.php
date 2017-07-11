<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Role;

class CampaignPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create events.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function createEvent(User $user, Campaign $campaign)
    {
        return in_array($user->id, $campaign->getUserByRole([Role::ROLE_OWNER, Role::ROLE_MODERATOR])->pluck('user_id')->toArray());
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function view(User $user, Campaign $campaign)
    {
        if (in_array($user->id, $campaign->blockeds()->pluck('id')->toArray())
            || $campaign->status == Campaign::BLOCK
        ) {
            return false;
        }

        return in_array($user->id, $campaign->users->pluck('id')->toArray())
            || $campaign->settings
                ->where('key', config('settings.campaigns.status'))
                ->where('value', config('settings.value_of_settings.status.public'))->isNotEmpty();
    }

    /**
     * Determine whether the user can join to the campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function joinCampaign(User $user, Campaign $campaign)
    {
        if (in_array($user->id, $campaign->blockeds()->pluck('id')->toArray())
            || $campaign->status == Campaign::BLOCK || !in_array($user->id, $campaign->blockeds()->pluck('id')->toArray())
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can leave to the campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function leaveCampaign(User $user, Campaign $campaign)
    {
        if (in_array($user->id, $campaign->blockeds()->pluck('id')->toArray())
            || $campaign->status == Campaign::BLOCK
        ) {
            return false;
        }

        return in_array($user->id, $campaign->users->pluck('id')->toArray());
    }

    /**
     * Determine whether the user can create campaigns.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        /**
         * Now, every user can create campaigns
         * but in the future, we'll change this
         * so I keep this function here
         */
        return true;
    }

    /**
     * Determine whether the user can manage the campaign. (update/delete)
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function manage(User $user, Campaign $campaign)
    {
        return $user->id === $campaign->owner()->first()->id;
    }

    /**
     * Determine whether the user can change status user in campaign.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function changeStatusUser(User $user, Campaign $campaign)
    {
        return in_array($user->id, $campaign->getUserByRole([Role::ROLE_OWNER, Role::ROLE_MODERATOR])->pluck('user_id')->all());
    }
}
