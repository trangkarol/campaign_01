<?php

namespace App\Repositories\Eloquent;

use Exception;
use App\Models\Media;
use App\Models\Campaign;
use App\Models\Activity;
use App\Notifications\InviteUser;
use App\Notifications\UserRequest;
use App\Notifications\AcceptRequest;
use App\Traits\Common\UploadableTrait;
use App\Repositories\Contracts\CampaignInterface;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknowException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Role;
use App\Models\User;
use App\Models\Donation;
use Carbon\Carbon;
use Notification;

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
        $this->updateMedia($campaign, $inputs['media']);
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
        if (!$campaign || str_contains($media, config('filesystems.disks.is_url_path'))) {
            return false;
        }

        $model = $campaign->media->first();

        if (!$model) {
            return false;
        }

        $oldUrl = $model->url_file;

        if (!empty($media)) {
            $urlFile = is_string($media)
                ? $this->parseBase64($media, 'campaigns')
                : $this->uploadFile($media, 'campaigns');
            $model->update(['url_file' => $urlFile]);
        }

        $this->destroyFile($oldUrl);

        return true;
    }

    public function delete($campaign)
    {
        $campaign->media()->delete();
        $campaign->likes()->delete();
        $campaign->settings()->delete();

        return $campaign->delete();
    }

    public function openCampaign($campaign)
    {
        $campaign->media()->restore();
        $campaign->likes()->restore();
        $campaign->settings()->restore();

        return $campaign->restore();
    }

    /**
     * get campaign.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function getCampaign($campaign, $userId)
    {
        $settings = $campaign->settings()->withTrashed()->whereIn('key', config('settings.campaigns'))->get();

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
        $campaign['campaign_images'] = $campaign->media()->withTrashed()->first();

        return [
            'campaign' => $campaign,
            'tags' => $campaign->tags()->withTrashed()->get(['name'])->flatten(),
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
            $result = $data['campaign']->users()->updateExistingPivot($data['userRequest']->id, [
                'status' => Campaign::APPROVED
            ]);
            $data['campaign']->activities()->create([
                'user_id' => $data['userRequest']->id,
                'name' => Activity::JOIN,
            ]);
            Notification::send($data['userRequest'], new AcceptRequest($data['sender'], $data['campaign']));

            return AcceptRequest::class;
        }

        $data['campaign']->users()->detach($data['user_id']);

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
     * user join and leave of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign, $userId
     * @return
     */
    public function attendCampaign($campaign, $user)
    {
        $campaign->users()->toggle([
            $user->id => [
                'role_id' => app(RoleRepository::class)->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)->id,
                'status' => Campaign::APPROVING,
            ]
        ]);

        $ownerCampaign = $campaign->owner;
        $moderatorsCampaign = $campaign->moderators;
        $listReceiver = $ownerCampaign->merge($moderatorsCampaign);
        Notification::send($listReceiver->all(), new UserRequest($user, $campaign));

        return [
            'listReceiver' => $listReceiver,
            'model' => UserRequest::class,
        ];
    }

    /**
     * list photos of campaign that mean photo of action
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign
     * @return
     */
    public function listPhotos($campaign)
    {
        return $campaign->events()
            ->with(['actions' => function ($query) {
                $query->with(['user', 'media' => function ($subQuery) {
                    $subQuery->where('type', Media::IMAGE)
                        ->groupBy('created_at')
                        ->orderBy('created_at', 'desc');
                }])
                ->groupBy('created_at')
                ->orderBy('created_at', 'desc')->first();
            }])
            ->groupBy('created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.paginate_default'));
    }

    /**
     * get campaign related
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign
     * @return mixed
    */
    public function getCampaignRelated($campaign, $user)
    {
        $tagIds = $campaign->tags()->pluck('tag_id')->all();
        $enday = Carbon::today()->format('Y-m-d');
        $campaignIds = $user->campaigns->pluck('id')->all();

        $campaign = $campaignCheckLike = $this->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })
        ->where('campaigns.status', Campaign::ACTIVE)
        ->where('campaigns.id', '!=', $campaign->id)
        ->whereNotIn('campaigns.id', $campaignIds);

        $data = [];
        $data['campaign'] = $campaign->with('media')
            ->paginate(config('settings.paginate_default'));

        $data['checkLiked'] = $this->checkLike($campaignCheckLike, $user->id);

        return $data;
    }

    public function searchCampaign($roleIds, $page, $quantity, $keyword)
    {
        $this->setGuard('api');
        $resutCampaign = $this->search($keyword, null, true)
            ->where(function($query) {
                $query->whereHas('settings', function ($subQuery) {
                    $subQuery->where('key', config('settings.campaigns.status'))
                        ->where('value', config('settings.value_of_settings.status.public'));
                })
                ->whereHas('settings', function ($subQuery) {
                    $subQuery->where('key', config('settings.campaigns.end_day'))
                        ->where('value', '>', Carbon::now()->format('m/d/Y'));
                });
            })
            ->orWhere(function($query) use ($roleIds) {
                $query->whereHas('users', function ($subQuery) use ($roleIds) {
                    $subQuery->where('campaign_user.status', Campaign::APPROVED)
                        ->whereIn('role_id', $roleIds)
                        ->where('user_id', $this->user->id);
                });
            })
            ->with('media', 'owner', 'tags', 'events', 'members', 'isMember', 'isOwner')
            ->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->get();

        return [
            'campaigns' => $resutCampaign->forPage($page, $quantity),
            'totalCampaign' => $resutCampaign->count(),
        ];
    }

    public function getStatisticData($campaign)
    {
        $campaignUsers = $campaign->getUserByRole(['owner', 'moderator', 'member']);
        $users['count'] = $campaignUsers->count();
        $users['today_count'] = $campaignUsers->wherePivot('created_at', '>=', Carbon::today())->count();
        $userStatistic = $campaignUsers
            ->select(\DB::raw('count(*) as user_count, date(campaign_user.created_at) as date'))
            ->groupBy('date')
            ->get();
        $users['chart_label'] = $userStatistic->pluck('date');
        $users['chart_data'] = $userStatistic->pluck('user_count');
        $events['count'] = $campaign->events()->withTrashed()->count();
        $events['finished'] = $campaign->events()->withTrashed()->whereHas('settings', function ($query) {
            $query->where([
                ['key', '=', config('settings.events.end_day')],
                ['value', '<', Carbon::now()],
            ]);
        })->count();
        $events['upcoming'] = $campaign->events()->withTrashed()->whereHas('settings', function ($query) {
            $query->where([
                ['key', '=', config('settings.events.start_day')],
                ['value', '>', Carbon::now()],
            ]);
        })->count();
        $events['ongoing'] = $campaign->events()->withTrashed()->count() - ($events['finished'] + $events['upcoming']);
        $eventStatistic = $campaign->events()->withTrashed()
            ->select(\DB::raw('count(*) as event_count, date(events.created_at) as date'))
            ->groupBy('date')
            ->get();
        $events['chart_label'] = $eventStatistic->pluck('date');
        $events['chart_data'] = $eventStatistic->pluck('event_count');
        $actions['count'] = $campaign->actions()->withTrashed()->count();
        $actions['today_count'] = $campaign->actions()->withTrashed()->where('actions.created_at', '>=', Carbon::today())->count();
        $actionStatistic = $campaign->actions()->withTrashed()
            ->select(\DB::raw('count(*) as action_count, date(actions.created_at) as date'))
            ->groupBy('date')
            ->get();
        $actions['chart_label'] = $actionStatistic->pluck('date');
        $actions['chart_data'] = $actionStatistic->pluck('action_count');
        $data = [ 'users' => $users, 'events' => $events, 'actions' => $actions ];

        return $data;
    }

    public function getExportData(Campaign $campaign)
    {
        return $campaign
            ->load([
                'media',
                'owner',
                'activeUsers',
                'settings' => function ($setting) {
                    $setting->whereIn('key', [
                        config('settings.campaigns.status'),
                        config('settings.campaigns.start_day'),
                        config('settings.campaigns.end_day'),
                    ]);
                },
                'events.settings' => function ($setting) {
                    $setting->whereIn('key', [
                        config('settings.events.start_day'),
                        config('settings.events.end_day'),
                    ]);
                },
                'events.media' => function ($media) {
                    $media->withTrashed();
                },
                'events.goals.donations' => function ($donation) {
                    $donation->where('status', Donation::ACCEPT);
                },
                'events.goals.donationType.quality',
                'events.goals.expenses',
            ]);
    }

    public function getEventsClosed($campaignId)
    {
        return $this->find($campaignId)
            ->events()
            ->onlyTrashed()
            ->with(['media' => function ($query) {
                $query->withTrashed();
            }])
            ->withCount(['actions' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('deleted_at', 'DESC')
            ->paginate(config('settings.paginate_default'));
    }

    public function getFriendIds($roleIds)
    {
        $this->setGuard('api');
        $friendIds = $this->user->friends()->pluck('id')->all();
        $campaignsUserJoined = $this->user
            ->campaigns()
            ->whereIn('role_id', $roleIds)
            ->where('campaign_user.status', Campaign::APPROVED)
            ->get()
            ->pluck('id');

        return [
            'friendIds' => $friendIds,
            'campaignIds' => $campaignsUserJoined,
        ];
    }

    public function getCampaignInvolve($roleIds)
    {
        $friendIds = $this->getFriendIds($roleIds)['friendIds'];
        $campaignsUserJoined = $this->getFriendIds($roleIds)['campaignIds'];
        $campaignsInvolve = $this->whereHas('users', function ($query) use (
            $roleIds,
            $friendIds,
            $campaignsUserJoined
        ) {
            $query->where('campaign_user.status', Campaign::APPROVED)
                ->whereIn('role_id', $roleIds)
                ->whereIn('user_id', $friendIds)
                ->whereNotIn('campaign_id', $campaignsUserJoined);
            })
            ->whereHas('settings', function ($query) {
                $query->where('key', config('settings.campaigns.status'))
                    ->where('value', config('settings.value_of_settings.status.public'));
            })
            ->whereHas('settings', function ($query) {
                $query->where('key', config('settings.campaigns.end_day'))
                    ->where('value', '>', Carbon::now()->format('Y-m-d'))
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('key', config('settings.campaigns.end_day'))
                            ->where('value', '');
                    });
            })
            ->with('media', 'tags')
            ->inRandomOrder()
            ->take(config('settings.campaigns_involve'))
            ->get();

        if ($campaignsInvolve->isEmpty()) {
            $campaignsInvolve = $this->whereHas('settings', function ($query) {
                $query->where('key', config('settings.campaigns.status'))
                    ->where('value', config('settings.value_of_settings.status.public'));
                })
                ->whereHas('settings', function ($query) {
                    $query->where('key', config('settings.campaigns.end_day'))
                        ->where('value', '>', Carbon::now()->format('Y-m-d'))
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('key', config('settings.campaigns.end_day'))
                                ->where('value', '');
                        });
                })
                ->with('media', 'tags')
                ->inRandomOrder()
                ->take(config('settings.campaigns_involve'))
                ->get();
        }

        return $campaignsInvolve;
    }

    public function getHomepage() {
        $totalCampaign = $this->count();
        $campaigns = $this->whereHas('settings', function ($query) {
            $query->where('key', config('settings.campaigns.status'))
                ->where('value', config('settings.value_of_settings.status.public'));
        })
        ->model
        ->with('media', 'tags', 'owner')
        ->withCount('activeUsers')
        ->inRandomOrder()
        ->take(config('settings.campaigns_public'))
        ->get();

        return [
            'data' => $campaigns,
            'totalCampaign' => $totalCampaign,
        ];
    }

    /**
     *invite user join to campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign, $userId
     * @return
     */
    public function inviteUser($data)
    {
        $this->setGuard('api');
        Notification::send($data['invitedUser'], new InviteUser($this->user, $data['campaign']));

        return $data['campaign']->users()->toggle([
            $data['invitedUser']->id => [
                'role_id' => $data['roleIdMember'],
                'status' => Campaign::REQUEST_USER,
                'is_manager' => $data['is_manager'],
            ]
        ]);
    }

    /**
     *accpet invitation user join to campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $campaign, $userId
     * @return
     */
    public function acceptInvitation($data)
    {
        $member = $data['campaign']->users()->wherePivot('user_id', $data['userId'])->first();
        $status = Campaign::APPROVING;

        if ($member->pivot->is_manager) {
            $status = Campaign::APPROVED;
        }

        $data['campaign']->users()->updateExistingPivot($data['userId'], [
            'status' => $status,
        ]);

        return $member->pivot->is_manager;
    }
}
