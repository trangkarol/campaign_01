<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Contracts\CampaignInterface;
use App\Repositories\Contracts\RoleInterface;
use App\Repositories\Contracts\TagInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\CommentInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\UserInterface;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknowException;
use App\Http\Requests\CampaignRequest;
use App\Notifications\InviteUser;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Activity;
use Notification;
use Exception;
use LRedis;

class CampaignController extends ApiController
{
    private $roleRepository;
    private $tagRepository;
    private $eventRepository;
    private $campaignRepository;
    private $commentRepository;
    private $actionRepository;
    private $userRepository;
    private $redis;

    public function __construct(
        CampaignInterface $campaignRepository,
        RoleInterface $roleRepository,
        TagInterface $tagRepository,
        EventInterface $eventRepository,
        CommentInterface $commentRepository,
        ActionInterface $actionRepository,
        UserInterface $userRepository
    ) {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->tagRepository = $tagRepository;
        $this->eventRepository = $eventRepository;
        $this->campaignRepository = $campaignRepository;
        $this->commentRepository = $commentRepository;
        $this->actionRepository = $actionRepository;
        $this->userRepository = $userRepository;
    }

    public function store(CampaignRequest $request)
    {
        $data = $request->only(
            'title',
            'description',
            'hashtag',
            'longitude',
            'latitude',
            'tags',
            'settings',
            'media',
            'address'
        );

        $data['role_id'] = $this->roleRepository->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id;
        $data['user_id'] = $this->user->id;

        return $this->doAction(function () use ($data) {
            $data['tags'] = $this->tagRepository->getOrCreate($data['tags']);
            $feature = $this->getValueSetting($data['settings'], config('settings.campaigns.status'), [0]);
            $this->compacts['campaign'] = $this->campaignRepository->create($data);
            $this->redis = LRedis::connection();
            $this->redis->publish('createCampaign', json_encode([
                'feature' => head($feature),
                'info' => $this->compacts['campaign']->load('media', 'tags'),
                'user' => $this->user,
                'name' => Activity::CREATE,
                'type' => $this->campaignRepository->model(),
            ]));
        });
    }

    public function destroy($id)
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        if (!$this->user->can('manage', $campaign)) {
            throw new UnknowException('You do not have authorize to delete this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($campaign) {
            $eventIds = $campaign->events()->pluck('id');
            $this->compacts['event'] = $this->eventRepository->delete($campaign->events());
            $this->compacts['campaign_tag'] = $this->tagRepository->deleteFromCampaign($campaign);
            $this->compacts['campaign_user'] = $this->userRepository->deleteFromCampaign($campaign);
            $this->compacts['campaign'] = $this->campaignRepository->delete($campaign);
            $this->compacts['actions'] = $this->actionRepository->deleteAction($eventIds);
        });
    }

    public function changeStatusMember($campaignId, $userId, $flag)
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);
        $userRequest = $this->userRepository->findOrFail($userId);
        $data = [
            'campaign' => $campaign,
            'flag' => $flag,
            'userRequest' => $userRequest,
            'sender' => $this->user,
        ];

        if ($this->user->cannot('changeStatusUser', $campaign)) {
            throw new UnknowException('You do not have authorize to change status user in this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $acceptRequestModel = $this->campaignRepository->changeStatusUser($data);
            $this->compacts['change_status'] = $data['userRequest'];
            $this->sendNotification(
                $data['userRequest']->id,
                $data['campaign'],
                $acceptRequestModel,
                config('settings.type_notification.campaign')
            );
        });
    }

    public function edit($id)
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        if ($this->user->cannot('manage', $campaign)) {
            throw new NotFoundException('Access denied', ACCESS_DENIED);
        }

        return $this->getData(function () use ($campaign) {
            $this->compacts['campaign'] = $campaign->load('media', 'settings', 'tags');
            $this->compacts['totalUser'] = $campaign->users()->count();
        });
    }

    public function update(CampaignRequest $request, $id)
    {
        $data = $request->only(
            'title',
            'description',
            'hashtag',
            'longitude',
            'latitude',
            'settings',
            'tags',
            'media'
        );

        $campaign = $this->campaignRepository->findOrFail($id);

        if ($this->user->cannot('manage', $campaign)) {
            throw new Exception('Policy fail');
        }

        $limit = $this->getValueSetting($data['settings'], config('settings.campaign.limit'), null);

        if (head($limit)['value'] && $campaign->users()->count() < head($limit)['value']) {
            throw new Exception('Setting error');
        }

        return $this->doAction(function () use ($data, $campaign) {
            $this->compacts['campaign'] = $this->campaignRepository->update($campaign, $data);
        });
    }

    /**
     * show campaign the first.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function show($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cant('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;

        return $this->getData(function () use ($campaign, $roleIdBlocked) {
            $this->compacts['events'] = $this->eventRepository->getEvent($campaign->events(), $this->user->id);
            $this->compacts['checkLiked'] = $this->eventRepository->checkLike($campaign->events(), $this->user->id);
            $this->compacts['show_campaign'] = $this->campaignRepository->getCampaign($campaign, $this->user->id);
            $this->compacts['members'] = $this->campaignRepository->getMembers($campaign, Campaign::APPROVED, $roleIdBlocked);
        });
    }

    /**
     * show list user.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function getListUser($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign) {
            $this->compacts['user'] = $this->campaignRepository->getListUser($campaign);
        });
    }

    /**
     * show campaign timeline.
     *
     * @param  array  $data
     * @return $campaign
    */
    public function getListEvent($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new NotFoundException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign) {
            $this->compacts['events'] = $this->eventRepository->getEvent($campaign->events(), $this->user->id);
            $this->compacts['checkLiked'] = $this->eventRepository->checkLike($campaign->events(), $this->user->id);
        });
    }

    public function like($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new Exception('Policy fail');
        }

        return $this->doAction(function () use ($campaign) {
            $this->compacts['campaign'] = $this->campaignRepository->createOrDeleteLike($campaign, $this->user->id);
        });
    }

    /**
     * Set role for member who join in campaign
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeMemberRole(Request $request)
    {
        $data = $request->only('campaignId', 'userId', 'roleId');
        $campaign = $this->campaignRepository->findOrFail($data['campaignId']);

        if ($this->user->cannot('changeRole', $campaign)) {
            throw new NotFoundException('You do not have authorize to change role this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data, $campaign) {
            $this->campaignRepository->changeMemberRole($campaign, $data['userId'], $data['roleId']);
        });
    }

    /**
     * Remove user from campaign's user list
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeUser(Request $request)
    {
        $data = $request->only('campaignId', 'userId');
        $campaign = $this->campaignRepository->findOrFail($data['campaignId']);

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
            $this->campaignRepository->removeUser($campaign, $data['userId']);
        });
    }

    /**
     * Change owner permission for other user
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeOwner(Request $request)
    {
        $data = $request->only('campaignId', 'userId', 'roleId');
        $campaign = $this->campaignRepository->findOrFail($data['campaignId']);

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
            $this->campaignRepository->changeOwner($campaign, $data['userId'], $data['roleId']);
        });
    }

    public function getTags()
    {
        return $this->getData(function () {
            $this->compacts['tags'] = $this->tagRepository->get(['name', 'id']);
        });
    }

    public function members($campaignId, $status, Request $request)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($campaignId);
        $data = $request->only('search', 'roleId');
        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;

        if ($this->user->cannot('permission', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaignId, $status, $data) {
            $this->compacts['roles'] = $this->roleRepository->getRoles(Role::TYPE_CAMPAIGN);
            $this->compacts['members'] = $this->userRepository->searchMembers($campaignId, $status, $data['search'], $data['roleId']);
        });
    }

    public function searchMembers($campaignId, $status, Request $request)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($campaignId);
        $data = $request->only('search', 'roleId');
        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;

        if ($this->user->cannot('member', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaignId, $status, $data) {
            $this->compacts['roles'] = $this->roleRepository->getRoles(Role::TYPE_CAMPAIGN);
            $this->compacts['members'] = $this->userRepository->searchMembers($campaignId, $status, $data['search'], $data['roleId']);
        });
    }

    public function search($page, $quantity, $type, Request $request)
    {
        $keyword = $request->keyword;
        $roleIds = $this->roleRepository->whereIn('name', [
            Role::ROLE_MEMBER,
            Role::ROLE_OWNER,
            Role::ROLE_MODERATOR,
        ])->pluck('id')->all();

        return $this->getData(function () use ($roleIds, $keyword, $page, $quantity, $type) {
            if (in_array($type, ['user', 'all'])) {
                $resutUser = $this->userRepository->searchUser($page, $quantity, $keyword);
                $this->compacts['users'] = $resutUser['users'];
                $this->compacts['totalUser'] = $resutUser['totalUser'];
            }

            if (in_array($type, ['campaign', 'all'])) {
                $resutCampaign = $this->campaignRepository->searchCampaign($roleIds, $page, $quantity, $keyword);
                $this->compacts['campaigns'] = $resutCampaign['campaigns'];
                $this->compacts['totalCampaign'] = $resutCampaign['totalCampaign'];
            }
        });
    }

    /**
     * user request join to campaign
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function attendCampaign($id, $flag)
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        if ($flag == config('settings.flag_join')) {
            if ($this->user->cannot('joinCampaign', $campaign)) {
                throw new NotFoundException('You do not have authorize to join this campaign', UNAUTHORIZED);
            }
        } else {
            if ($this->user->cannot('leaveCampaign', $campaign)) {
                throw new NotFoundException('You do not have authorize to leave this campaign', UNAUTHORIZED);
            }
        }

        return $this->doAction(function () use ($campaign, $flag) {
            $data = $this->campaignRepository->attendCampaign($campaign, $this->user, $flag);
            $this->compacts['attend_campaign'] = $this->user;

            if ($flag == config('settings.flag_join')) {
                foreach ($data['listReceiver'] as $receiver) {
                    $this->sendNotification(
                        $receiver->id,
                        $campaign,
                        $data['model'],
                        config('settings.type_notification.campaign')
                    );
                }
            }
        });
    }

    /**
     * list photos of campaign
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function listPhotos($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new NotFoundException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign) {
            $eventIds = $campaign->events()->withTrashed()->pluck('id')->all();
            $this->compacts['list_photos'] = $this->actionRepository->getActionPhotos($eventIds, $this->user->id);
        });
    }

    /**
     * get campaign related
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCampaignRelated($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($campaign) {
            $this->compacts['campaign_related'] = $this->campaignRepository->getCampaignRelated($campaign, $this->user);
        });
    }

    public function statistic($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        return $this->getData(function () use ($campaign) {
            $this->authorize('view', $campaign);
            $this->compacts['data'] = $this->campaignRepository->getStatisticData($campaign);
        });
    }

    public function getExportData($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        return $this->getData(function () use ($campaign) {
            $this->authorize('view', $campaign);
            $this->compacts['data'] = $this->campaignRepository->getExportData($campaign);
        });
    }

    public function getEventsClosed($campaignId)
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        if ($this->user->cannot('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        $isManager = $this->user->can('manage', $campaign);

        return $this->getData(function () use ($campaignId, $isManager) {
            $this->compacts['eventsClosed'] = $this->campaignRepository->getEventsClosed($campaignId);
            $this->compacts['isManager'] = $isManager;
        });
    }

    public function getCampaignInvolve()
    {
        $roleIds = $this->roleRepository->whereIn('name', [
            Role::ROLE_MEMBER,
            Role::ROLE_OWNER,
            Role::ROLE_MODERATOR,
        ])->pluck('id')->all();

        return $this->getData(function () use ($roleIds) {
            $this->compacts['campaignInvolve'] = $this->campaignRepository->getCampaignInvolve($roleIds);
        });
    }

    public function openCampaign($id)
    {
        $campaign = $this->campaignRepository->withTrashed()->findOrFail($id);

        if ($this->user->cannot('manage', $campaign)) {
            throw new UnknowException('You do not have authorize to open this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($campaign) {
            $eventIds = $campaign->events()->withTrashed()->pluck('id');
            $this->compacts['event'] = $this->eventRepository->openFromCampaign($campaign->events());
            $this->compacts['campaign_tag'] = $this->tagRepository->openFromCampaign($campaign);
            $this->compacts['campaign_user'] = $this->userRepository->openFromCampaign($campaign);
            $this->compacts['campaign'] = $this->campaignRepository->openCampaign($campaign);
            $this->compacts['actions'] = $this->actionRepository->openAction($eventIds);
        });
    }

    public function getHomepage()
    {
        return $this->getData(function () {
            $this->compacts['totalUser'] = $this->userRepository->count();
            $this->compacts['campaigns'] = $this->campaignRepository->getHomepage();
        });
    }

    private function getValueSetting($settings, $conditions, $valueIfNotFound)
    {
        if (!is_array($settings)) {
            return $valueIfNotFound;
        }

        return array_where($settings, function ($value, $key) use ($conditions, $valueIfNotFound) {
            return !empty($value['key'])
                ? $value['key'] == $conditions
                : $valueIfNotFound;
        });
    }

    public function searchMemberToInvites($campaignId, Request $request)
    {
        $data = $request->only('search');
        $data['campaign'] = $this->campaignRepository->withTrashed()->findOrFail($campaignId);
        $data['roleIdBlocked'] = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;
        $data['campaignId'] = $campaignId;
        $data['userId'] = $this->user->id;

        if ($this->user->cannot('inviteUser', $data['campaign'])) {
            throw new UnknowException('You do not have authorize to invite member to join this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($data) {
            $firendIds = $this->user->friends('users.id')->pluck('id')->all();

            $this->compacts['members'] = $this->userRepository->searchMemberToInvite($data, $firendIds);
        });
    }

    public function inviteUser($campaignId, $userId)
    {
        $data = [];
        $data['invitedUser'] = $this->userRepository->findOrFail($userId);
        $data['campaign'] = $this->campaignRepository->findOrFail($campaignId);
        $data['roleIdMember'] = $this->roleRepository->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)->id;

        if ($this->user->cannot('inviteUser', $data['campaign'])) {
            throw new UnknowException('You do not have authorize to invite member to join this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $data['is_manager'] = $this->user->can('permission', $data['campaign'])
                ? config('settings.is_manager')
                : config('settings.not_manager');
            $this->compacts['members'] = $this->campaignRepository->inviteUser($data);
            $this->sendNotification(
                $data['invitedUser']->id,
                $data['campaign'], InviteUser::class,
                config('settings.type_notification.campaign')
            );
        });
    }

    public function acceptInvitation($campaignId)
    {
        $data = [];
        $data['userId'] = $this->user->id;
        $data['campaign'] = $this->campaignRepository->findOrFail($campaignId);

        if ($this->user->cannot('view', $data['campaign'])) {
            throw new UnknowException('You do not have authorize to accept', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $this->compacts['user'] = $this->user;
            $this->compacts['is_manager'] = $this->campaignRepository->acceptInvitation($data);
        });
    }
}
