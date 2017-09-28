<?php

namespace App\Repositories\Contracts;

interface ActionInterface extends RepositoryInterface
{
    public function getActionPhotos($eventIds, $userId);

    public function showAction($action, $userId);

    public function deleteFromEvent($actions);

    public function openFromEvent($event);

    public function deleteAction($eventIds);

    public function createFromExpense($data, $goal);

    public function updateFromExpense($data, $goal);

    public function deleteFromExpense($expenseId);

    public function getOneAction($id);

    public function getActionIds($eventIds);
}
