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
use Illuminate\Http\Request;
use App\Http\Requests\CampaignRequest;
use App\Models\Role;
use App\Models\Campaign;
use Exception;

class CampaignController extends ApiController
{
    private $roleRepository;
    private $tagRepository;
    private $eventRepository;
    private $campaignRepository;
    private $commentRepository;
    private $actionRepository;
    private $userRepository;

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
            $this->compacts['campaign'] = $this->campaignRepository->create($data);
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
            $this->compacts['event'] = $this->eventRepository->delete($eventIds);
            $this->compacts['campaign'] = $this->campaignRepository->delete($campaign);
        });
    }

    public function changeStatusMember($campaignId, $userId, $flag)
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        $data = [
            'campaign' => $campaign,
            'user_id' => $userId,
            'flag' => $flag,
        ];

        if ($this->user->cannot('changeStatusUser', $campaign)) {
            throw new UnknowException('You do not have authorize to change status user in this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $this->campaignRepository->changeStatusUser($data);
            $this->compacts['change_status'] = $this->userRepository->findOrFail($data['user_id']);
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
        $campaign = $this->campaignRepository->findOrFail($id);

        if ($this->user->cant('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;
        $userId = $this->user->id;

        return $this->getData(function () use ($campaign, $userId, $roleIdBlocked) {
            $this->compacts['events'] = $this->eventRepository->getEvent($campaign->events());
            $this->compacts['show_campaign'] = $this->campaignRepository->getCampaign($campaign, $userId);
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
        $campaign = $this->campaignRepository->findOrFail($id);

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
        $campaign = $this->campaignRepository->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new NotFoundException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign) {
            $this->compacts['events'] = $this->eventRepository->getEvent($campaign->events());
        });
    }

    public function like($id)
    {
        $campaign = $this->campaignRepository->findOrFail($id);

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

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
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
        $data = $request->only('campaign_id', 'user_id');
        $campaign = $this->campaignRepository->findOrFail($data['campaign_id']);

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
            $this->campaignRepository->removeUser($campaign, $data['user_id']);
        });
    }

    /**
     * Change owner permission for other user
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeOwner(Request $request)
    {
        $data = $request->only('campaign_id', 'user_id', 'role_id');
        $campaign = $this->campaignRepository->findOrFail($data['campaign_id']);

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
            $this->campaignRepository->changeOwner($campaign, $data['user_id'], $data['role_id']);
        });
    }

    public function getTags()
    {
        return $this->getData(function () {
            $this->compacts['tags'] = $this->tagRepository->get(['name', 'id']);
        });
    }

    public function members($campaignId, $status)
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);
        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;

        if ($this->user->cannot('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign, $status, $roleIdBlocked) {
            $this->compacts['roles'] = $this->roleRepository->getRoles(Role::TYPE_CAMPAIGN);
            $this->compacts['members'] = $this->campaignRepository->getMembers($campaign, $status, $roleIdBlocked);
        });
    }

    public function searchMembers($campaignId, $status, Request $request)
    {

        $campaign = $this->campaignRepository->findOrFail($campaignId);
        $data = $request->only('search', 'roleId');
        $roleIdBlocked = $this->roleRepository->findRoleOrFail(Role::ROLE_BLOCKED, Role::TYPE_CAMPAIGN)->id;

        if ($this->user->cannot('view', $campaign)) {
            throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaignId, $status, $data) {
            $this->compacts['roles'] = $this->roleRepository->getRoles(Role::TYPE_CAMPAIGN);
            $this->compacts['members'] = $this->userRepository->searchMembers($campaignId, $status, $data['search'], $data['roleId']);
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

        $user = $this->user;

        return $this->doAction(function () use ($user, $campaign) {
            $this->campaignRepository->attendCampaign($campaign, $user->id);
            $this->compacts['attend_campaign'] = $user;
        });
    }

    /**
     * list photos of campaign
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function listPhotos($id)
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        if ($this->user->cannot('view', $campaign)) {
            throw new NotFoundException('You do not have authorize to see this campaign', UNAUTHORIZED);
        }

        return $this->getData(function () use ($campaign) {
            $eventIds = $campaign->events()->pluck('id')->all();
            $actions = $this->actionRepository->whereIn('event_id', $eventIds);
            $this->compacts['list_photos'] = $this->actionRepository->getActionPhotos($actions);
        });
    }

    /**
     * get campaign related
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCampaignRelated($id)
    {
        $campaign = $this->campaignRepository->findOrFail($id);
        $userId = $this->user->id;

        return $this->doAction(function () use ($campaign, $userId) {
            $this->compacts['campaign_related'] = $this->campaignRepository->getCampaignRelated($campaign, $userId);
        });
    }
}
