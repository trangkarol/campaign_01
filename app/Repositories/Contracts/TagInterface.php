<?php

namespace App\Repositories\Contracts;

interface TagInterface extends RepositoryInterface
{
    public function getOrCreate($tags);

    public function deleteFromCampaign($campaign);
}
