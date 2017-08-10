<?php

namespace App\Repositories\Contracts;

interface ActionInterface extends RepositoryInterface
{
    public function createOrDeleteLike($action, $userId);

    public function deleteFromEvent($event);

    public function getActionPhotos($eventIds, $userId);

    public function showAction($action, $userId);
}
