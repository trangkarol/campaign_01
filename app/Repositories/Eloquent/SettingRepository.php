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

        $campaignIds = $this->user->campaigns()
            ->withTrashed()
            ->where('campaign_user.status', Campaign::ACTIVE)
            ->whereIn('campaign_user.role_id', $roles)
            ->pluck('campaign_id')
            ->all();
        $campaignsPublic = $this->withTrashed()->where('settingable_type', Campaign::class)
            ->where('key', config('settings.campaigns.status'))
            ->where('value', config('settings.value_of_settings.status.public'))
            ->groupBy('settingable_id')
            ->pluck('settingable_id')
            ->all();

        return array_unique(array_merge($campaignIds, $campaignsPublic));
    }
}
