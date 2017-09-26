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
        if ($campaign->blockeds()->pluck('id')->contains($user->id)
            || $campaign->status == Campaign::BLOCK
        ) {
            return false;
        }

        $public = $campaign->settings()->withTrashed()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'))
            ->get()
            ->isNotEmpty();

        $private = $campaign->settings()->withTrashed()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.private'))
            ->get()
            ->isNotEmpty();

        return $public || ($campaign->users->contains('id', $user->id) && $private);
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
        if ($campaign->blockeds()->pluck('id')->contains($user->id)
            || $campaign->status == Campaign::BLOCK || $campaign->users()->pluck('user_id')->contains($user->id)
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
        if ($campaign->blockeds()->pluck('id')->contains($user->id)
            || $campaign->status == Campaign::BLOCK || !$campaign->users()->pluck('user_id')->contains($user->id)
        ) {
            return false;
        }

        return true;
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

    /**
     * Determine whether the user can join the chat group of campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function joinChat(User $user, Campaign $campaign)
    {
        if ($campaign->blockeds()->pluck('id')->contains($user->id)
            || $campaign->status == Campaign::BLOCK
        ) {
            return false;
        }

        return $campaign->users->pluck('id')->contains($user->id);
    }

    /**
     * Determine whether the user can manage the campaign. (update/delete)
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function permission(User $user, Campaign $campaign)
    {
        if ($user->id == $campaign->owner()->first()->id ||
            $campaign->moderators()->pluck('user_id')->contains($user->id)) {
            return true;
        }

        return false;
    }

    public function like(User $user, Campaign $campaign)
    {
        $public = $campaign->settings()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'))
            ->get()
            ->isNotEmpty();

        $private = $campaign->settings()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.private'))
            ->get()
            ->isNotEmpty();

        return $public || ($campaign->users()->wherePivot('status', Campaign::APPROVED)->pluck('user_id')->contains($user->id) && $private);
    }

    public function comment(User $user, Campaign $campaign)
    {
         $public = $campaign->settings()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'))
            ->get()
            ->isNotEmpty();

        $private = $campaign->settings()
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.private'))
            ->get()
            ->isNotEmpty();

        return $public || ($campaign->users()->wherePivot('status', Campaign::APPROVED)->pluck('user_id')->contains($user->id) && $private);
    }

    /**
     * Determine whether the user can search member to invite the campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function inviteUser(User $user, Campaign $campaign)
    {
        $private = $campaign->settings
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.private'));
        $ownerId = $campaign->owner()->first()->id;
        $moderatorIds = $campaign->moderators()->pluck('user_id');

        if ($private->isNotEmpty() && ($user->id == $ownerId || $moderatorIds->contains($user->id))) {
            return true;
        }

        $public = $campaign->settings
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'));

        if ($campaign->users->pluck('id')->contains($user->id)
            && $public->isNotEmpty()) {
            return true;
        }

        return false;
    }

    public function member(User $user, Campaign $campaign)
    {
        return $campaign->users->contains('id', $user->id);
    }

    public function changeRole(User $user, Campaign $campaign)
    {
        return $campaign->getUserByRole([Role::ROLE_OWNER, Role::ROLE_MODERATOR])
            ->pluck('user_id')
            ->contains($user->id);
    }
}
