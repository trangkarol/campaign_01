<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Role;
use App\Models\Campaign;
use App\Models\Action;
use App\Models\Activity;
use App\Models\Event;
use App\Repositories\Contracts\UserInterface;
use App\Jobs\SendEmail;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Exceptions\Api\UnknowException;
use App\Traits\Common\UploadableTrait;
use App\Notifications\MakeFriend;
use Notification;
use Carbon\Carbon;

class UserRepository extends BaseRepository implements UserInterface
{
    use DispatchesJobs, UploadableTrait;

    public function model()
    {
        return User::class;
    }

    public function active($token)
    {
        $user = $token ? $this->where('token_confirm', $token)->first() : false;

        if (!$user) {
            return false;
        }

        $this->update($user->id, [
            'status' => User::ACTIVE,
            'token_confirm' => null,
        ]);

        return $user;
    }

    public function register($inputs, $roleId)
    {
        $user = $this->create($inputs);

        if (!$user) {
            throw new UnknowException('Had errors while processing');
        }

        $user->roles()->attach($roleId);

        // Send email active to user
        $info = [
            'email' => $inputs['email'],
            'subject' => trans('emails.active_subject'),
        ];

        $fields = [
            'content' => trans('emails.active_account', ['object' => $user->name]),
        ];

        $this->dispatch(new SendEmail($info, User::ACTIVE_LINK_SEND, $fields));

        return $fields;
    }

    /**
     * List all campaigns that the user owns
     * @return Illuminate\Pagination\Paginator
     */
    public function ownedCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc')
    {
        $data = [];
        $campaign = $campaignCheckLike = $this->findOrFail($id)->campaigns()
            ->getLikes()
            ->with('users', 'owner', 'media')
            ->wherePivot('role_id', app(RoleRepository::class)
                ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id);

        $data['campaign'] = $campaign
            ->orderBy($orderBy, $direction)
            ->simplePaginate(2);

        $data['checkLiked'] = $this->checkLike($campaignCheckLike, $userId)->all();

        return $data;
    }

    /**
     * List all campaigns that the user join
     * @return Illuminate\Pagination\Paginator
     */
    public function joinedCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc')
    {
        $data = [];
        $campaign = $campaignCheckLike = $this->findOrFail($id)->campaigns()
            ->getLikes()
            ->with('users', 'owner', 'media')
            ->wherePivotIn('role_id', app(RoleRepository::class)->findRoleOrFail([
                Role::ROLE_OWNER,
                Role::ROLE_MODERATOR,
                Role::ROLE_MEMBER,
            ], Role::TYPE_CAMPAIGN)->pluck('id')->all());

        $data['campaign'] = $campaign
            ->orderBy($orderBy, $direction)
            ->simplePaginate(2);

        $data['checkLiked'] = $this->checkLike($campaignCheckLike, $userId)->all();

        return $data;
    }

    public function getTimeline($user, $data, $userId)
    {
        $currentUser = $user;
        $user = $user->activities()
            ->with(['activitiable' => function ($query) {
                $query->with('media')
                    ->getLikes();
            }])
            ->whereIn('activitiable_type', [
                Campaign::class,
                Action::class,
                Event::class,
            ])
            ->where('name', Activity::CREATE);
        if ($currentUser->id == $userId) {
            $user = $user->where(function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereIn('activitiable_id', $data['eventIds'])
                        ->where('activitiable_type', Event::class);
                })
                ->orWhere(function ($sub) use ($data) {
                    $sub->where('activitiable_type', Campaign::class)
                        ->whereIn('activitiable_id', $data['campaignIds']);
                })
                ->orWhere(function ($sub) use ($data) {
                    $sub->where('activitiable_type', Action::class)
                        ->whereIn('activitiable_id', $data['actionIds']);
                });
            });
        }

        $activities = $checkLikeActivities = $user;

        // $checkLikes = $user->activities()->whereIn('id', $checkLikeActivities->get()->pluck('id'))
        //     ->with(['activitiable' => function ($query) use ($userId) {
        //         $query->whereHas('likes', function ($subQuery) use ($userId) {
        //             $subQuery->where('user_id', $userId);
        //         });
        //     }])
        //     ->get();

        // $checkLiked = $checkLikes->reject(function ($item, $key) {
        //     return $item->activitiable === null;
        // });

        $inforListActivity = $activities->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->paginate(config('setting.pagin-tion.timeline'));

        $listActivity = $inforListActivity->each(function ($item) {
            if ($item->activitiable_type == Campaign::class) {
                $item->load('activitiable.tags', 'activitiable.settings');
            } else {
                if ($item->activitiable_type == Event::class) {
                    $item = $item->load('activitiable.campaign');
                } else {
                    $item = $item->load('activitiable.event.campaign');
                }

                //comment
                $item->load(['comments' => function ($query) {
                    $query->withTrashed()
                        ->getLikes()
                        ->where('parent_id', config('settings.comment_parent'))
                        ->orderBy('created_at', 'desc')
                        ->paginate(config('settings.paginate_comment'), ['*'], 1);
                }]);
            }
        });

        return [
            'currentPageUser' => $currentUser,
            'inforListActivity' => $inforListActivity,
            'listActivity' => $listActivity,
            'checkLiked' => []//$checkLiked->pluck('activitiable_id'),
        ];
    }

    /**
     * List all campaigns that the user are following through tag
     * @param  int $id
     * @param  string $orderBy
     * @param  string $direction
     * @return Illuminate\Pagination\Paginator
     */
    public function listFollowingCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc')
    {
        $data = [];
        $campaign = $campaignCheckLike = Campaign::with('users', 'owner', 'media')
            ->getLikes()
            ->whereHas('tags', function ($query) use ($id) {
                $query->whereIn('campaign_tag.tag_id', \App\Models\User::findOrFail($id)->tags->pluck('id'));
            });

        $data['campaign'] = $campaign
            ->distinct()
            ->orderBy($orderBy, $direction)
            ->simplePaginate(config('settings.pagination.following_campaign'));

        $data['checkLiked'] = $this->checkLike($campaignCheckLike, $userId)->all();

        return $data;
    }

    /**
     * List all tags that the user are following
     * @param  int $id
     * @param  string $orderBy
     * @param  string $direction
     * @return Illuminate\Pagination\Paginator
     */
    public function listFollowingTag($id, $orderBy = 'created_at', $direction = 'desc')
    {
        $user = $this->findOrFail($id);

        return $user
            ->tags()
            ->orderBy($orderBy, $direction)
            ->simplePaginate();
    }

    /**
     * User join or quit campaign
     * @param  int $campaignId
     */
    public function joinCampaign($campaignId)
    {
        \Auth::guard('api')
            ->user()
            ->campaigns()
            ->toggle([$campaignId => ['status' => Campaign::APPROVING]]);
    }

    /**
     * Multiupload images
     * @param  array  $files
     * @param  string $path
     */
    public function uploadImages(array $files, $path)
    {
        foreach ($files as $key => $file) {
            $uploadedFiles[$key]['url_file'] = $this->uploadFile($file, $path);
            $uploadedFiles[$key]['type'] = \App\Models\Media::IMAGE;
        }

        return \Auth::guard('api')
            ->user()
            ->media()
            ->createMany($uploadedFiles);
    }

    /**
     * search members of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $id
     * @return boolen
     */
    public function searchMembers($campaignId, $status, $search, $roleId)
    {
        $user = $search ? $this->search($search, null, true) : $this;

        return $user->where('status', User::ACTIVE)
            ->whereHas('campaigns', function ($query) use ($campaignId, $status, $roleId) {
                $query = $query->withTrashed()->where('campaign_id', $campaignId)
                    ->where('campaign_user.status', $status);

                if ($roleId != config('settings.search_default')) {
                    $query = $query->where('campaign_user.role_id', $roleId);
                }
            })
            ->with(['campaigns' => function ($query) use ($campaignId) {
                $query->withTrashed()->where('campaign_id', $campaignId);
            }])
            ->paginate(config('settings.paginate_default'));
    }

    public function notificationMakeFriend($userRequest, $approvalId)
    {
        $user = $this->findOrFail($approvalId);
        Notification::send($user, new MakeFriend($this->getIfUser($userRequest)));
    }

    public function getNotifications($user, $skip, $type)
    {
        return $this->getIfUser($user)
            ->notifications()
            ->whereIn('type', is_array($type) ? $type : [$type])
            ->skip($skip)
            ->take(config('settings.paginate_notification'))
            ->get();
    }

    public function countUnreadNotifications($user, $type)
    {
        return $this->getIfUser($user)
            ->unreadNotifications()
            ->whereIn('type', is_array($type) ? $type : [$type])
            ->count();
    }

    public function markRead($typeNoty, $user)
    {
        if ($typeNoty != config('settings.read_notification_friend')) {
            return false;
        }

        $type[] = MakeFriend::class;
        $this->getIfUser($user)
            ->unreadNotifications()
            ->whereIn('type', $type)
            ->get()
            ->markAsRead();

        return true;
    }

    public function deleteNotification($id, $user, $type)
    {
        if ($type) {
            $this->getIfUser($user)
                ->notifications()
                ->where('id', $id)
                ->delete();
        } else {
            $this->findOrFail($id)
                ->notifications()
                ->where('notifiable_type', User::class)
                ->where('data', 'like', '%{"to":' . $id . ',"form":{"id":' . $this->getIfUser($user)->id . '%')
                ->delete();
        }

        return true;
    }

    private function getIfUser($user)
    {
        if (!$user instanceof User) {
            throw new Exception('Object is not user');
        }

        return $user;
    }

    public function searchUser($page, $quantity, $keyword)
    {
        $this->setGuard('api');
        $resutUser = $this->search($keyword, null, true)
            ->where('status', USER::ACTIVE)
            ->where('id', '<>', $this->user->id)
            ->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->get();

        return [
            'users' => $resutUser->forPage($page, $quantity)->each(function ($query) {
                $query->makeVisible([
                    'is_friend',
                    'has_pending_request',
                    'has_send_request',
                ]);
            }),
            'totalUser' => $resutUser->count(),
        ];
    }

    public function closedCampaign($user, $roleIdManagement)
    {
        return $user->campaigns()
            ->with(['media' => function ($query) {
                $query->withTrashed();
            }])
            ->onlyTrashed()
            ->with('owner')
            ->wherePivotIn('role_id', $roleIdManagement)
            ->withTrashed()
            ->groupBy('campaigns.deleted_at')
            ->orderBy('campaigns.deleted_at', 'DESC')
            ->paginate(config('settings.paginate_default'));
    }

    public function deleteFromCampaign($campaign)
    {
        if (!is_null($campaign)) {
            $currentDay = Carbon::Now();
            $campaign->users->each(function ($user) use ($campaign, $currentDay) {
                $user->campaigns()->updateExistingPivot($campaign->id, ['deleted_at' => $currentDay]);
            });

            return true;
        }

        return false;
    }

    public function openFromCampaign($campaign)
    {
        if (!is_null($campaign)) {
            $campaign->users->each(function ($user) use ($campaign) {
                $user->campaigns()->updateExistingPivot($campaign->id, ['deleted_at' => null]);
            });

            return true;
        }

        return false;
    }

    /**
     * search members to invite join to campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $id
     * @return boolen
     */
    public function searchMemberToInvite($data, $firendIds)
    {
        $memberIds = $data['campaign']->users->pluck('id')->all();
        $user = $data['search'] ? $this->search($data['search'], null, true) : $this;

        return $user->whereIn('id', $firendIds)
            ->whereNotIn('id', $memberIds)
            ->paginate(config('settings.paginate_member'));
    }

    public function getFriendsSuggest()
    {
        $this->setGuard('api');
        $countMutualFriends = [];
        $friendIds = $this->user->friends()->pluck('id')->all();
        $friendsPedding = $this->user->pendingFriends()->pluck('users.id')->all();
        $friendsIAmSender = $this->user->acceptFriends()->pluck('users.id')->all();
        $friendIds[] = $this->user->id;
        $friendIds = array_merge($friendIds, $friendsPedding, $friendsIAmSender);

        // if (count($friendIds)) {
        //     $friendSuggestIds = \DB::table('relationships')
        //         ->where('status', User::ACCEPTED)
        //         ->whereIn('sender_id', $friendIds)
        //         ->where('recipient_id', '<>' ,$this->user->id)
        //         ->whereNotIn('recipient_id', $friendIds)
        //         ->groupBy('recipient_id')
        //         ->pluck('recipient_id');
        //     $friendSuggests = $this->model
        //         ->where('status', User::ACTIVE)
        //         ->whereIn('id', $friendSuggestIds)
        //         ->inRandomOrder()
        //         ->take(config('settings.friend_suggest'))
        //         ->get()
        //         ->each(function ($query) use ($friendIds) {
        //             $query->makeVisible([
        //                 'is_friend',
        //                 'has_pending_request',
        //                 'has_send_request',
        //             ]);
        //         });

        $friendSuggests = $this->where('status', User::ACTIVE)
            ->whereNotIn('id', $friendIds)
            ->inRandomOrder()
            ->take(config('settings.friend_suggest'))
            ->get();

        foreach ($friendSuggests as $user) {
            $countMutualFriends[] = count(array_intersect($user->friends()->pluck('id')->all(), $friendIds));
        }

        return [
            'countMutualFriends' => $countMutualFriends,
            'friendSuggests' => $friendSuggests,
        ];
    }
}
