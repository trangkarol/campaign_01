<?php

namespace App\Repositories\Contracts;

interface UserInterface extends RepositoryInterface
{
    public function active($token);

    public function register($inputs, $roleId);

    public function ownedCampaign($id, $orderBy = 'created_at', $direction = 'desc');

    public function joinedCampaign($id, $orderBy = 'created_at', $direction = 'desc');

    public function listFollowingCampaign($id, $orderBy = 'created_at', $direction = 'desc');

    public function listFollowingTag($id, $orderBy = 'created_at', $direction = 'desc');

    public function joinCampaign($campaignId);

    public function uploadImages(array $files, $path);

    public function searchMembers($campaignId, $status, $search, $roleId);
}
