<?php

namespace App\Repositories\Contracts;

interface EventInterface extends RepositoryInterface
{
    public function delete($eventIds);

    public function update($event, $inputs);

    public function getEvent($campaign, $userId);

    public function createOrDeleteLike($event, $userId);
}
