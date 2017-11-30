<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Repositories\Contracts\CampaignInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\CommentInterface;
use App\Repositories\Contracts\ActionInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class CloseCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ColseCampaign:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Colse campaign!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        CampaignInterface $campaignRepository,
        EventInterface $eventRepository,
        CommentInterface $commentRepository,
        ActionInterface $actionRepository
    ) {
        parent::__construct();
        $this->eventRepository = $eventRepository;
        $this->campaignRepository = $campaignRepository;
        $this->commentRepository = $commentRepository;
        $this->actionRepository = $actionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $campaigns = $this->campaignRepository->findCampaignExpired();

            foreach ($campaigns as $campaign) {
                $eventIds = $campaign->events()->pluck('id');
                $this->eventRepository->delete($campaign->events());
                $this->tagRepository->deleteFromCampaign($campaign);
                $this->userRepository->deleteFromCampaign($campaign);
                $this->campaignRepository->delete($campaign);
                $this->actionRepository->deleteAction($eventIds);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
