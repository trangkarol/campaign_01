<?php

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Models\Campaign;
use App\Repositories\Contracts\SettingInterface;
use App\Exceptions\Api\NotFoundException;
use App\Models\Role;

class SettingRepository extends BaseRepository implements SettingInterface
{
    public function model()
    {
        return Setting::class;
    }

    public function getCampaignIds()
    {
        $this->setGuard('api');
        $rolesName = [Role::ROLE_OWNER, Role::ROLE_MODERATOR, Role::ROLE_MEMBER];
        $role = app('App\Repositories\Eloquent\RoleRepository');
        $roles = $role->whereIn('name', $rolesName)
            ->where('type', Role::TYPE_CAMPAIGN)
            ->lists('id');
        $campaignIds = $this->user->campaigns
            ->where('status', Campaign::ACTIVE)
            ->where('pivot.status', Campaign::APPROVED)
            ->whereIn('pivot.role_id', $roles)
            ->pluck('id')
            ->all();

        $campaignsClose = $this->user->campaigns()
            ->onlyTrashed()
            ->pluck('campaign_id')
            ->all();

        $campaignsPublic = $this->where('settingable_type', Campaign::class)
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'))
            ->whereNotIn('settingable_id', $campaignsClose)
            ->groupBy('settingable_id')
            ->pluck('settingable_id')
            ->all();

        return array_unique(array_merge($campaignIds, $campaignsPublic));
    }
}
