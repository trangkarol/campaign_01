<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use App\Repositories\Contracts\RoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCampaignRequest;
use App\Models\Role;

class RoleController extends ApiController
{
    protected $roleRepository;

    public function __construct(
        RoleInterface $roleRepository
    ) {
        parent::__construct();
        $this->roleRepository = $roleRepository;
    }

    public function getRoleCampaign()
    {
        return $this->getData(function () {
            $this->compacts['role'] = $this->roleRepository->findRoleOrFail(['*'], Role::TYPE_CAMPAIGN);
        });
    }
}
