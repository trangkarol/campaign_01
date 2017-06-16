<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Contracts\CampaignInterface;
use App\Repositories\Contracts\RoleInterface;
use App\Repositories\Contracts\TagInterface;
use App\Repositories\Contracts\EventInterface;
use App\Exceptions\Api\NotFoundException;
use App\Http\Requests\CreateCampaignRequest;
use App\Exceptions\Api\UnknowException;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Campaign;

class CampaignController extends ApiController
{
    private $roleRepository;
    private $tagRepository;
    private $eventRepository;
    private $campaignRepository;

    public function __construct(
        CampaignInterface $campaignRepository,
        RoleInterface $roleRepository,
        TagInterface $tagRepository,
        EventInterface $eventRepository
    ) {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->tagRepository = $tagRepository;
        $this->eventRepository = $eventRepository;
        $this->campaignRepository = $campaignRepository;
    }

    protected function create(CreateCampaignRequest $request)
    {
        $data = $request->only('title', 'description', 'hashtag', 'longitude', 'latitude', 'tags', 'settings', 'media');

        $data['role_id'] = $this->roleRepository->getRoleId(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN);

        if (!$data['role_id']) {
            throw new NotFoundException('Not found role when create campaign');
        }

        $data['user_id'] = $this->user->id;
        $data['tags'] = $this->tagRepository->getOrCreate($data['tags']);

        return $this->doAction(function () use ($data) {
            $this->compacts['campaign'] = $this->campaignRepository->create($data);
        });
    }

    protected function delete(Request $request)
    {
        $campaign = $this->campaignRepository->find($request->id);

        if (!$campaign) {
            throw new UnknowException('NOT_FOUND', NOT_FOUND);
        }

        if (!$this->user->can('manage', $campaign)) {
            throw new UnknowException('UNAUTHORIZED', UNAUTHORIZED);
        }

        return $this->doAction(function () use ($campaign) {
            $eventIds = $campaign->events()->pluck('id');
            $this->campacts['event'] = $this->eventRepository->delete($eventIds);
            $this->campacts['campaign'] = $this->campaignRepository->delete($campaign);
        });
    }

    /**
     * search campaign of user.
     *
     * @return void
     */
    public function search(Request $request)
    {
        $data = $request->only(
            'tag_id',
            'hashtag',
            'title',
            'longitude',
            'latitude',
            'date_start',
            'date_end'
        );

        $data['status'] = Campaign::STATUS_PUBLIC;

        return $this->getData(function () use ($data) {
            $this->compacts['campaign'] = $this->campaignRepository->search($data);
        });
    }
}
