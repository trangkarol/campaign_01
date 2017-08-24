<?php

namespace App\Repositories\Contracts;

interface UserInterface extends RepositoryInterface
{
    public function active($token);

    public function register($inputs, $roleId);

    public function ownedCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc');

    public function joinedCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc');

    public function listFollowingCampaign($id, $userId, $orderBy = 'created_at', $direction = 'desc');

    public function listFollowingTag($id, $orderBy = 'created_at', $direction = 'desc');

    public function joinCampaign($campaignId);

    public function uploadImages(array $files, $path);

    public function searchMembers($campaignId, $status, $search, $roleId);

    public function notificationMakeFriend($userRequest, $approvalId);

    public function getNotifications($user, $skip, $type);

    public function countUnreadNotifications($user, $type);

    public function markRead($typeNoty, $user);

    public function deleteNotification($id, $user, $type);

    public function closedCampaign($user, $roleIdManagement);

    public function deleteFromCampaign($campaign);
}
