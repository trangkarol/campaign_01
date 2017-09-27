<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\Repositories\Contracts\UserInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\TagInterface;
use App\Repositories\Contracts\SettingInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\SecurityRequest;
use App\Http\Requests\User\ImageUploadRequest;
use App\Exceptions\Api\UnknowException;
use App\Traits\Common\UploadableTrait;
use Carbon\Carbon;
use Hash;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Role;
use App\Repositories\Contracts\RoleInterface;
use LRedis;
use Exception;
use App\Notifications\MakeFriend;

class UserController extends ApiController
{
    use UploadableTrait;

    protected $tagRepository;
    protected $roleRepository;
    protected $settingRepository;
    protected $actionRepository;
    protected $eventRepository;
    private $redis;
    const READ = 0;

    public function __construct(
        UserInterface $userRepository,
        TagInterface $tagRepository,
        RoleInterface $roleRepository,
        SettingInterface $settingRepository,
        ActionInterface $actionRepository,
        EventInterface $eventRepository
    ) {
        parent::__construct($userRepository);
        $this->tagRepository = $tagRepository;
        $this->roleRepository = $roleRepository;
        $this->settingRepository = $settingRepository;
        $this->actionRepository = $actionRepository;
        $this->eventRepository = $eventRepository;
        $this->redis = LRedis::connection();
    }

    public function authUser()
    {
        try {
            $this->redis->publish('activies', json_encode([
                'userId' => $this->user->id,
                'listFollow' => $this->user
                    ->friends()
                    ->pluck('id')
                    ->all(),
                'status' => true,
            ]));
        } catch (Exception $e) {
            activity()->log($e->getMessage());

            return $this->responseFail();
        }

        return $this->user->load([
            'roles' => function ($query) {
                $query->where('type', Role::TYPE_SYSTEM);
            }
        ]);
    }

    /**
     * Update current user's password
     *
     * @param  App\Http\Requests\Api\User\SecurityRequest  $request;
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(SecurityRequest $request)
    {
        if (!Hash::check($request->get('current_password'), $this->user->password)) {
            throw new UnknowException('Current password is incorrect!', BAD_REQUEST);
        }

        return $this->doAction(function () use ($request) {
            $this->repository->update($this->user->id, [
                'password' => $request->get('password'),
            ]);
        });
    }

    /**
     * Update current user profile
     *
     * @param  App\Http\Requests\Api\User\ProfileRequest  $request;
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(ProfileRequest $request)
    {
        $request->has('birthday')
            ? $request->merge(['birthday' => Carbon::parse($request->get('birthday'))->toDateString()])
            : null;

        return $this->doAction(function () use ($request) {
            $user = $this->repository
                ->update($this->user->id, $request->only('name', 'birthday', 'address', 'phone', 'gender', 'about'));
            $this->compacts['user'] = $user;
        });
    }

    /**
     * Update current user's avatar
     * @return \Illuminate\Http\Response
     */
    public function updateAvatar(ImageUploadRequest $request)
    {
        return $this->doAction(function () use ($request) {
            $this->repository->update($this->user->id, ['url_file' => $request->get('url_file')]);
        });
    }

    /**
     * Update current user's header photo
     * @return \Illuminate\Http\Response
     */
    public function updateHeaderPhoto(ImageUploadRequest $request)
    {
        return $this->doAction(function () use ($request) {
            $this->repository->update($this->user->id, ['head_photo' => $request->get('url_file')]);
        });
    }

    /**
     * Multi upload images
     * @param  Request $request
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function uploadImages(ImageUploadRequest $request, $path)
    {
        return $this->doAction(function () use ($request, $path) {
            $this->compacts['images'] = $this->repository->uploadImages($request->uploads, $path);
        });
    }

    public function listFriends($id, $page)
    {
        $user = $this->repository->findOrFail($id);

        return $this->getData(function () use ($user, $page) {
            $this->compacts['data'] = $user->friends()->forPage($page, 4)->values();
        });
    }

    public function searchFriends($id, $keyword)
    {
        $user = $this->repository->findOrFail($id);

        return $this->getData(function () use ($user, $keyword) {
            if ($keyword == '') {
                return;
            }

            $this->compacts['data'] = $user->friends()->filter(function ($friend) use ($keyword) {
                return false !== stristr($friend->name, $keyword);
            })->flatten();
        });
    }

    /**
     * List all campaigns that the user owns
     * @return \Illuminate\Http\Response
     */
    public function listOwnedCampaign($id)
    {
        return $this->getData(function () use ($id) {
            $this->compacts['data'] = $this->repository->ownedCampaign($id, $this->user->id);
        });
    }

    /**
     * List all campaigns that the user join
     * @return \Illuminate\Http\Response
     */
    public function listJoinedCampaign($id)
    {
        return $this->getData(function () use ($id) {
            $this->compacts['data'] = $this->repository->joinedCampaign($id, $this->user->id);
        });
    }

    public function sendFriendRequestTo($id)
    {
        $user = $this->repository->findOrFail($id);

        if ($user->id == $this->user->id || $user->status == User::IN_ACTIVE) {
            throw new Exception('Error Processing Request');
        }

        return $this->doAction(function () use ($id) {
            $toggle = $this->user->friendsIAmSender()->toggle($id);
            $data['type'] = true;

            if ($toggle['attached']) {
                $this->repository->notificationMakeFriend($this->user, $id);
            } else {
                $this->repository->deleteNotification($id, $this->user, false);
                $data['type'] = false;
            }

            $data['to'] = $id;
            $data['noty'] = $this->user;
            $this->compacts['type'] = $data['type'];
            $this->redis->publish('noty', json_encode($data));
        });
    }

    public function acceptFriendRequestFrom(Request $request, $id)
    {
        return $this->doAction(function () use ($id, $request) {
            $this->user->pendingFriends()->updateExistingPivot($id, ['status' => User::ACCEPTED]);

            if ($request->id) {
                $this->repository->deleteNotification($request->id, $this->user, true);
            } else {
                $user = $this->repository->findOrFail($id);
                $this->repository->deleteNotification($this->user->id, $user, false);
            }
        });
    }

    public function denyFriendRequestFrom($id)
    {
        return $this->doAction(function () use ($id) {
            $this->user->friendsIAmRecipient()->detach($id);
            $user = $this->repository->findOrFail($id);
            $this->repository->deleteNotification($this->user->id, $user, false);
        });
    }

    public function unfriend($id)
    {
        return $this->doAction(function () use ($id) {
            $this->user->friendsIAmRecipient()->detach($id);
            $this->user->friendsIAmSender()->detach($id);
        });
    }

    /**
     * Toogle follow or unfollow a tag
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function followTag($id)
    {
        $this->tagRepository->findOrFail($id);

        return $this->doAction(function () use ($id) {
            $this->user->tags()->toggle($id);
        });
    }

    /**
     * List all campaigns that user are following (with tag)
     * @return \Illuminate\Http\Response
     */
    public function listFollowingCampaign($id)
    {
        return $this->doAction(function () use ($id) {
            $this->compacts['data'] = $this->repository->listFollowingCampaign($id, $this->user->id);
        });
    }

    public function listFollowingTag($id)
    {
        return $this->doAction(function () use ($id) {
            $this->compacts['data'] = $this->repository->listFollowingTag($id);
        });
    }

    /**
     * Join/quit a campaign
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function joinCampaign($id)
    {
        $this->repository->findOrFail($id);

        return $this->doAction(function () use ($id) {
            $this->repository->joinCampaign($id);
        });
    }

    /**
     * Timeline of user
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getTimeline($id)
    {
        $user = $this->repository->findOrFail($id)->makeVisible([
            'friends_count',
            'is_friend',
            'has_pending_request',
            'has_send_request',
        ]);

        if ($user->status == User::IN_ACTIVE) {
            throw new Exception('Error Processing Request');
        }

        return $this->getData(function () use ($user) {
            $data = [];
            $data['campaignIds'] = $this->settingRepository->getCampaignIds();
            $data['eventIds'] = $this->eventRepository->getEventIds($data['campaignIds']);
            $data['actionIds'] = $this->actionRepository->getActionIds($data['eventIds']);
            $this->compacts['data'] = $this->repository->getTimeline($user, $data, $this->user->id);
        });
    }


    public function getListFollow()
    {
        return $this->getData(function () {
            $this->compacts['followings'] = $this->user->friends([
                'users.id',
                'name',
                'email',
                'url_file',
            ]);

            $roleIds = $this->roleRepository->whereIn('name', [
                    Role::ROLE_OWNER,
                    Role::ROLE_MODERATOR,
                    Role::ROLE_MEMBER,
                ])
                ->lists('id')
                ->all();
            $this->compacts['groups'] = $this->user->campaigns()
                ->getLikes()
                ->wherePivot('status', Campaign::APPROVED)
                ->wherePivotIn('role_id', $roleIds)
                ->where('campaigns.status', Campaign::ACTIVE)
                ->with('media')
                ->get([
                    'campaigns.id',
                    'hashtag',
                    'title',
                ]);
        });
    }

    public function getPhotosAndFriends($userId)
    {
        $user = $this->repository->findOrFail($userId);

        if ($user->status == User::IN_ACTIVE) {
            throw new Exception('Error Processing Request');
        }

        return $this->getData(function () use ($user) {
            $this->compacts['listPhoto'] = $user->media()
                ->orderBy('id', 'DESC')
                ->take(config('settings.take_friend'))
                ->get();
            $this->compacts['listFriend'] = $user->friends()->take(config('settings.pagination.friend'));
        });
    }

    public function getPhotos($userId)
    {
        $user = $this->repository->findOrFail($userId);

        if ($user->status == User::IN_ACTIVE) {
            throw new Exception('Error Processing Request');
        }

        return $this->getData(function () use ($user) {
            $this->compacts['photos'] = $user->media()
                ->orderBy('id', 'DESC')
                ->paginate(config('settings.pagination.photo'));
        });
    }

    public function deletePhoto($mediaId)
    {
        $photo = $this->user->media()->findOrFail($mediaId);

        return $this->getData(function () use ($photo) {
            $this->compacts['photo'] = $photo->delete();
        });
    }

    public function getUser($id)
    {
        return $this->getData(function () use ($id) {
            $this->compacts['data'] = $this->repository->getTimeLine($id);
        });
    }

    public function markRead(Request $request)
    {
        return $this->doAction(function () use ($request) {
            $this->repository->markRead($request->type, $this->user);
            $this->compacts['unread'] = self::READ;
        });
    }

    public function getNotificationRequest(Request $request)
    {
        return $this->getData(function () use ($request) {
            $skip = json_decode($request->skip);
            $this->compacts['unread'] = $this->repository->countUnreadNotifications($this->user, MakeFriend::class);
            $this->compacts['notifications'] = $this->repository->getNotifications($this->user, $skip, MakeFriend::class);
            $this->compacts['skip'] = $skip += config('settings.paginate_notification');
            $this->compacts['continue'] = $this->compacts['notifications']->isNotEmpty();
        });
    }

    public function rejectRequest(Request $request)
    {
        return $this->doAction(function () use ($request) {
            $this->user->friendsIAmRecipient()->detach($request->userId);

            if ($request->id) {
                $this->repository->deleteNotification($request->id, $this->user, true);
            } else {
                $user = $this->repository->findOrFail($request->userId);
                $this->repository->deleteNotification($this->user->id, $user, false);
            }
        });
    }

    public function listClosedCampaign($id)
    {
        $roleIds = $this->roleRepository->whereIn('name', [
                Role::ROLE_OWNER,
                Role::ROLE_MODERATOR,
            ])
            ->where('type', Role::TYPE_CAMPAIGN)
            ->pluck('id')
            ->all();
        $user = $this->repository->findOrFail($id);

        return $this->getData(function () use ($user, $roleIds) {
            $this->compacts['closed_campaign'] = $this->repository->closedCampaign($user, $roleIds);
       });
    }

    public function friendsSuggest()
    {
        return $this->getData(function () {
            $this->compacts['data'] = $this->repository->getFriendsSuggest();
        });
    }

    public function listNotification()
    {
        return $this->getData(function () {
            $this->compacts['notifications'] = $this->repository->listNotification($this->user);
        });
    }

    public function totalUnreadNotifications()
    {
        return $this->getData(function () {
            $this->compacts['totalUnread'] = $this->repository->totalUnreadNotifications();
        });
    }

    public function markReadNotifications()
    {
        return $this->doAction(function () {
            $this->compacts['unread'] = $this->repository->markReadNotifications($this->user);
        });
    }
}
