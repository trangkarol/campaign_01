<?php

namespace App\Repositories\Eloquent;

use App\Models\Media;
use App\Models\Campaign;
use App\Models\Activity;
use App\Traits\Common\UploadableTrait;
use App\Repositories\Contracts\CampaignInterface;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknowException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

class CampaignRepository extends BaseRepository implements CampaignInterface
{
    use UploadableTrait;

    public function model()
    {
        return Campaign::class;
    }

    private function isArrayFormat($settings)
    {
        if (!$settings) {
            return false;
        }

        if (!is_array($settings)) {
            throw new UnknowException('Param is not an array');
        }

        //check each element is array
        foreach ($settings as $setting) {
            if (!is_array($setting)) {
                throw new UnknowException('Invalid format array');
            }
        }

        return true;
    }

    public function create($inputs)
    {
        if (is_null($inputs) || !is_array($inputs)) {
            throw new UnknowException('Inputs is null or not an array');
        }

        $data = [
            'title' => $inputs['title'],
            'hashtag' => $inputs['hashtag'],
            'description' => $inputs['description'],
            'longitude' => $inputs['longitude'],
            'latitude' => $inputs['latitude'],
            'status' => Campaign::ACTIVE,
            'address' => empty($inputs['address']) ? null : $inputs['address'],
        ];

        $campaign = parent::create($data);

        if (!$campaign) {
            throw new NotFoundException('Error create campaign');
        }

        if (array_key_exists('settings', $inputs) && $this->isArrayFormat($inputs['settings'])) {
            $campaign->settings()->createMany($inputs['settings']);
        }

        if (!empty($inputs['media'])) {
            $urlFile = is_string($inputs['media'])
                ? $this->parseBase64($inputs['media'], 'campaigns')
                : $this->uploadFile($inputs['media'], 'campaigns');
            $campaign->media()->create([
                'url_file' => $urlFile,
                'type' => Media::IMAGE,
            ]);
        }

        $campaign->activities()->create([
            'user_id' => $inputs['user_id'],
            'name' => Activity::CREATE,
        ]);
        $campaign->users()->attach($inputs['user_id'], [
            'role_id' => $inputs['role_id'],
            'status' => Campaign::APPROVED,
        ]);

        if ($inputs['tags'] && is_array($inputs['tags'])) {
            $campaign->tags()->attach($inputs['tags']['old']);
            $campaign->tags()->createMany($inputs['tags']['new']);
        }

        return $campaign;
    }

    public function update($campaign, $inputs)
    {
        $this->deleteOrCreateTags($campaign, $inputs['tags']);
        $this->updateSettings($campaign, $inputs['settings']);
        $this->updateMedia($inputs['media'], $campaign);
        $campaign = parent::update($campaign->id, array_except($inputs, ['tags', 'settings', 'media']));

        return $campaign;
    }

    private function deleteOrCreateTags($campaign, $tags)
    {
        if (!$this->isArrayFormat($tags)) {
            return false;
        }

        if (!$campaign->tags->isEmpty() && !$tags) {
            $campaign->tags()->detach($campaign->tags->pluck('id'));
        }

        if ($campaign->tags->isEmpty() && !$tags) {
            return false;
        }

        $oldIds = [];
        $newTags = [];

        foreach ($tags as $tag) {
            if ($tag['id']) {
                $oldIds[] = $tag['id'];
            } else {
                $newTags[] = ['name' => $tag['name']];
            }
        }

        $deleteIds = array_diff($campaign->tags->pluck('id')->toArray(), $oldIds);

        if ($deleteIds) {
            $campaign->tags()->detach($deleteIds);
        }

        if ($newTags) {
            $campaign->tags()->createMany($newTags);
        }

        return true;
    }

    private function updateSettings($campaign, $settings)
    {
        if (!$campaign || !$this->isArrayFormat($settings)) {
            return false;
        }

        $settingsCampaign = $campaign->settings;

        if ($settingsCampaign->isEmpty()) {
            return false;
        }

        foreach ($settings as $name => $setting) {
            $model = $settingsCampaign->where('key', $setting['key'])->first();

            if ($model) {
                $model->update(['value' => $setting['value']]);
            }
        }

        return true;
    }

    private function updateMedia($campaign, $media)
    {
        if (!$campaign || !is_file($media)) {
            return false;
        }

        $model = $campaign->media->first();

        if (!$model) {
            return false;
        }

        $this->destroyFile($model->url_file);
        $urlFile = $this->uploadFile($media, 'campaigns');
        $model->update(['url_file' => $urlFile]);

        return true;
    }

    public function delete($campaign)
    {
        $campaign->donations()->delete();
        $campaign->tags()->detach();
        $campaign->users()->detach();
        $campaign->media()->delete();
        $campaign->likes()->delete();
        $campaign->settings()->delete();

        return $campaign->delete();
    }

    /**
     * get campaign.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function getCampaign($campaign, $userId)
    {
        $settings = $campaign->settings()->whereIn('key', config('settings.campaigns'))->get();

        $campaign['status'] = $settings->where('key', config('settings.campaigns.status'))->first();
        $campaign['limit'] = $settings->where('key', config('settings.campaigns.limit'))->first('value');
        $campaign['start_day'] = $settings->where('key', config('settings.campaigns.start_day'))->first('value');
        $campaign['end_day'] = $settings->where('key', config('settings.campaigns.end_day'))->first('value');
        $campaign['timeout_campaign'] = $settings->where('key', config('settings.campaigns.timeout_campaign'))->first('value');
        // members in campaign
        $campaign['check_status'] = $campaign->users()->wherePivot('user_id', $userId)->first();
        $campaign['owner'] = $campaign->owner;
        $campaign['check_moderators'] = $campaign->moderators()->wherePivot('user_id', $userId)->pluck('user_id')->all();
        $campaign['check_owner'] = $campaign->owner()->wherePivot('user_id', $userId)->pluck('user_id')->all();
        // images campaign
        $campaign['campaign_images'] = $campaign->media()->first();

        return [
            'campaign' => $campaign,
            'tags' => $campaign->tags()->get(['name'])->flatten(),
        ];
    }

    /**
     * get getListUser.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function getListUser($campaign)
    {
        $data = [];
        $data['members'] = $campaign->with('users')->get();
        $data['memberIds'] = $data['members']->pluck('user_id')->all();

        return $data;
    }

    public function createOrDeleteLike($campaign, $userId)
    {
        if (!is_numeric($userId) || !$campaign) {
            return false;
        }

        if ($campaign->likes->where('user_id', $userId)->isEmpty()) {
            return $this->createByRelationship('likes', [
                'model' => $campaign,
                'attribute' => ['user_id' => $userId],
            ]);
        }

        return $campaign->likes()->where('user_id', $userId)->first()->forceDelete();
    }

    /**
     * Set role for member who join in campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $userId
     * @param  int $roleId
     * @return mix
     */
    public function changeMemberRole($campaign, $userId, $roleId)
    {
        if (!in_array($roleId, app(RoleRepository::class)->findRoleOrFail('*', Role::TYPE_CAMPAIGN)->pluck('id')->all())
            || !in_array($userId, $campaign->users->pluck('id')->all())) {
            throw new UnknowException('Invalid data input');
        }

        return $campaign->users()->syncWithoutDetaching([$userId => ['role_id' => $roleId]]);
    }

    /**
     * Remove user from campaign's user list
     * @param  App\Models\Campaign $campaign
     * @param  int $userId
     * @return mix
     */
    public function removeUser($campaign, $userId)
    {
        if (!in_array($userId, $campaign->users->pluck('id')->all())) {
            throw new UnknowException('This user is not a member of this campaign');
        }

        if (auth()->id() == $userId) {
            throw new UnknowException('Can not remove yourself');
        }

        return $campaign->users()->detach($userId);
    }

    /**
     * Change owner permission for other user
     * @param  App\Models\Campaign $campaign
     * @param  int $userId
     * @param  int $roleId  Role of owner after transfer
     * @return mixed
     */
    public function changeOwner($campaign, $userId, $roleId)
    {
        $ownerRoleId = app(RoleRepository::class)->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id;
        $this->changeMemberRole($campaign, $campaign->owner()->first()->id, $roleId);
        $this->changeMemberRole($campaign, $userId, $ownerRoleId);
    }

    /**
     * Change status of user when join to campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $data, $status
     * @return boolen
     */
    public function changeStatusUser($data)
    {
        if ($data['flag'] == Campaign::FLAG_APPROVE) {
            $result = $data['campaign']->users()->updateExistingPivot($data['user_id'], ['status' => Campaign::APPROVED]);
            $data['campaign']->activities()->create([
                'user_id' => $data['user_id'],
                'name' => Activity::JOIN,
            ]);

            return $result;
        } else {
            $data['campaign']->users()->detach($data['user_id']);
        }

        return true;
    }

    /**
     * get list members of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $id
     * @return boolen
     */
    public function getMembers($campaign, $status, $roleIdBlocked)
    {
        return $campaign->users()
           ->wherePivot('status', $status)
           ->wherePivot('role_id', '!=', $roleIdBlocked)
           ->orderBy('created_at', 'desc')
           ->paginate(config('settings.paginate_default'));
    }

    /**
     * search members of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $id
     * @return boolen
     */
    public function searchMembers($campaign, $status, $search)
    {
        return $campaign->whereHas('users', function ($query) use ($status, $search) {
            if (!is_null($search)) {
                $query = $query->where('users.name', 'like', '%' . $search . '%');
            }
            $query->where('campaign_user.status', $status);
        })->paginate(config('settings.paginate_default'));
        // $user = $this->where('status', User::ACTIVE);

        // if (!is_null($search)) {
        //     $user = $user->where('name', 'like', '%' . $search . '%');
        // }

        // return $user->whereHas('campaigns', function ($query) use ($campaignId, $status) {
        //     $query->where('campaign_id', $campaignId)
        //         ->where('campaign_user.status', $status);
        // })->paginate(config('settings.paginate_default'));
    }

    /**
     * user join and leave of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign, $userId
     * @return
     */
    public function attendCampaign($campaign, $userId)
    {
        return $campaign->users()->toggle([
            $userId => [
                'role_id' => app(RoleRepository::class)->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)->id,
                'status' => Campaign::APPROVING,
            ]
        ]);
    }

    /**
     * list photos of campaign that mean photo of action
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign
     * @return
     */
    public function listPhotos($campaign)
    {
        return $campaign->events()->with(['actions' => function ($query) {
            $query->with(['user', 'media' => function ($subQuery) {
                $subQuery->where('type', Media::IMAGE)->orderBy('created_at', 'desc');
            }])->orderBy('created_at', 'desc')->first();
        }])->orderBy('created_at', 'desc')->paginate(config('settings.paginate_default'));
    }

    /**
     * get campaign related
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign
     * @return mixed
    */
    public function getCampaignRelated($campaign, $userId)
    {
        $tagIds = $campaign->tags()->pluck('tag_id')->all();
        $enday = Carbon::today()->format('Y-m-d');

        $listCampaigns =  $this->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })
        ->whereHas('settings', function ($query) use ($enday) {
            $query->where('key', config('settings.campaigns.end_day'))
                ->where('value', '<=', $enday);
        })
        ->where('status', Campaign::ACTIVE)
        ->where('id', '!=', $campaign->id);

        // get campaigns that user is login joined
        $campaignIds = $listCampaigns->users->where('id', $userId)->pluck('pivot.campaign_id');

        return $listCampaigns->whereNotIn('id', $campaignIds)->with('users', 'media', 'tags')
        ->paginate(config('settings.paginate_default'));
    }

    /**
     * statistics memebers join to campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign
     * @return mixed
    */
    public function statisticsMembers($campaign)
    {
        $members = $campaign->users;

        // $ages = $members->each(function ($item, $key) {
        //     $date = Carbon::parse($item->created_at);
        //     $month = $date->month;
        // });
        // $ages = $campaign->users->groupBy(function ($date) {
        //     // return Carbon::parse($date->created_at)->format('Y'); // grouping by years
        //     //return Carbon::parse($date->created_at)->format('m'); // grouping by months
        // });
        // foreach ($members as $item) {
        //     $date = Carbon::parse($item->created_at);
        //     $month = $date->month;
        // }

        // dd($ages);
    }
}
