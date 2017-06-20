<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Contracts\CampaignInterface;
use App\Repositories\Contracts\RoleInterface;
use App\Repositories\Contracts\TagInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\CommentInterface;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknowException;
use Illuminate\Http\Request;
use App\Http\Requests\CampaignRequest;
use App\Models\Role;
use Exception;
use App\Models\Campaign;

class CampaignController extends ApiController
{
    private $roleRepository;
    private $tagRepository;
    private $eventRepository;
    private $campaignRepository;
    private $commentRepository;

    public function __construct(
        CampaignInterface $campaignRepository,
        RoleInterface $roleRepository,
        TagInterface $tagRepository,
        EventInterface $eventRepository,
        CommentInterface $commentRepository
    ) {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->tagRepository = $tagRepository;
        $this->eventRepository = $eventRepository;
        $this->campaignRepository = $campaignRepository;
        $this->commentRepository = $commentRepository;
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
            'media'
        );

        $data['role_id'] = $this->roleRepository->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id;
        $data['user_id'] = $this->user->id;
        $data['tags'] = $this->tagRepository->getOrCreate($data['tags']);

        return $this->doAction(function () use ($data) {
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

    public function approveUserJoinCampaign(Request $request)
    {
        $data = $request->only('user_id', 'campaign_id');
        $data['campaign'] = $this->campaignRepository->findOrFail($data['campaign_id']);

        if ($this->user->cannot('changeStatusUser', $data['campaign'])) {
            throw new UnknowException('You do not have authorize to change status user in this campaign', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($data) {
            $this->compacts['campaign'] = $this->campaignRepository->changeStatusUser($data, Campaign::APPROVED);
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

        // if ($this->user->cant('view', $campaign)) {
        //     throw new UnknowException('You do not have authorize to see this campaign', UNAUTHORIZED);
        // }

        return $this->getData(function () use ($campaign) {
            $this->compacts['events'] = $this->eventRepository->getEvent($campaign->events());
            // dd($this->eventRepository->getEvent($campaign->events())->toArray());
            // $this->compacts['comments'] = $this->commentRepository->getComment($campaign->events());
            // $eventIds = $campaign->events()->pluck('id')->all();
            // dd($eventIds, $this->commentRepository->getComment($eventIds));
            // if (!$campaign->events()->get()->isEmpty()) {
            //     $this->compacts['events'] = $this->paginateData(
            //         $this->eventRepository->getEvent($campaign->events())
            //     );
            // }

            $roleIdOwner = $this->roleRepository->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id;
            $this->compacts['show_campaign'] = $this->campaignRepository->getCampaign($campaign, $roleIdOwner);
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
            throw new NotFoundException('You do not have authorize to see this campaign', UNAUTHORIZED);
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

        // if ($this->user->cannot('view', $campaign)) {
        //     throw new NotFoundException('You do not have authorize to delete this campaign', UNAUTHORIZED);
        // }

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
        $data = $request->only('campaign_id', 'user_id', 'role_id');
        $campaign = $this->campaignRepository->findOrFail($data['campaign_id']);

        return $this->doAction(function () use ($data, $campaign) {
            $this->authorize('manage', $campaign);
            $this->campaignRepository->changeMemberRole($campaign, $data['user_id'], $data['role_id']);
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
}
