<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\PassportService;
use App\Repositories\Contracts\UserInterface;
use App\Models\User;
use App\Models\Role;
use App\Exceptions\Api\UnknowException;
use App\Exceptions\Api\NotFoundException;
use LRedis;
use Exception;

class AuthController extends ApiController
{
    protected $dataSelected = [
        'id',
        'name',
        'email',
        'birthday',
        'address',
        'phone',
        'url_file',
    ];

    public function __construct(UserInterface $userRepository)
    {
        parent::__construct($userRepository);
    }

    public function login(LoginRequest $request, PassportService $passport)
    {
        $data = $request->only('email', 'password');

        $user = $this->repository->select($this->dataSelected)->where('email', $data['email'])
            ->with(['roles' => function ($query) {
                $query->where('type', Role::TYPE_SYSTEM);
            }])
            ->first();

        if (!$user) {
            throw new NotFoundException('Not Found user');
        }

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'status' => User::ACTIVE])) {
            throw new UnknowException('User not active or credential invalid');
        }

        return $this->getData(function () use ($passport, $data, $user) {
            $this->compacts['auth'] = $passport->requestGrantToken($data);
            $this->compacts['user'] = $user;
            LRedis::publish('activies', json_encode([
                'userId' => $user->id,
                'listFollow' => $user->friends()->pluck('id')->all(),
                'status' => true,
            ]));
        });
    }

    public function logout()
    {
        if (!$this->user) {
            throw new UnknowException('Access token was invalid');
        }

        try {
            $this->user->token()->revoke();
            LRedis::publish('activies', json_encode([
                'userId' => $this->user->id,
                'status' => false,
            ]));
        } catch (Exception $e) {
            return $this->responseFail();
        }

        return $this->trueJson();
    }
}
