<?php

namespace App\Repositories\Contracts;

interface CampaignInterface extends RepositoryInterface
{
    public function create($inputs);

    public function delete($campaignId);

    public function update($campaign, $inputs);

    public function getCampaign($campaign, $userId);

    public function getListUser($campaign);

    public function createOrDeleteLike($campaign, $userId);

    public function changeMemberRole($campaign, $userId, $roleId);

    public function removeUser($campaign, $userId);

    public function changeOwner($campaign, $userId, $roleId);

    public function changeStatusUser($data);

    public function getMembers($campaign, $status, $roleIdBlocked);

    public function listPhotos($campaign);

    public function getCampaignRelated($campaign, $userId);

    public function statisticsMembers($campaign);

    public function searchMembers($campaign, $status, $search);
}
