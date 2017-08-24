<?php

namespace App\Repositories\Contracts;

interface ActionInterface extends RepositoryInterface
{
    public function createOrDeleteLike($action, $userId);

    public function getActionPhotos($eventIds, $userId);

    public function showAction($action, $userId);

    public function deleteFromEvent($actions);

    public function openFromEvent($event);

    public function deleteAction($eventIds);
}
