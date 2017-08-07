<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Role;
use App\Models\Campaign;
use App\Models\Action;
use App\Models\event;
use App\Repositories\Contracts\UserInterface;
use App\Jobs\SendEmail;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Exceptions\Api\UnknowException;
use App\Traits\Common\UploadableTrait;

class UserRepository extends BaseRepository implements UserInterface
{
    use DispatchesJobs, UploadableTrait;

    public function model()
    {
        return User::class;
    }

    public function active($token)
    {
        $user = $token ? $this->where('token_confirm', $token)->first() : false;

        if (!$user) {
            return false;
        }

        $this->update($user->id, [
            'status' => User::ACTIVE,
            'token_confirm' => null,
        ]);

        return true;
    }

    public function register($inputs, $roleId)
    {
        $user = $this->create($inputs);

        if (!$user) {
            throw new UnknowException('Had errors while processing');
        }

        $user->roles()->attach($roleId);

        // Send email active to user
        $info = [
            'email' => $inputs['email'],
            'subject' => trans('emails.active_subject'),
        ];

        $fields = [
            'linkActive' => action('Frontend\UserController@active', $user->token_confirm),
            'content' => trans('emails.active_account', ['object' => $user->name]),
        ];

        $this->dispatch(new SendEmail($info, User::ACTIVE_LINK_SEND, $fields));

        return $fields;
    }

    /**
     * List all campaigns that the user owns
     * @return Illuminate\Pagination\Paginator
     */
    public function ownedCampaign($id, $orderBy = 'created_at', $direction = 'desc')
    {
        return $this->findOrFail($id)->campaigns()
            ->with('users', 'owner', 'media')
            ->wherePivot('role_id', app(RoleRepository::class)->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)->id)
            ->orderBy($orderBy, $direction)
            ->simplePaginate(2);
    }

    /**
     * List all campaigns that the user join
     * @return Illuminate\Pagination\Paginator
     */
    public function joinedCampaign($id, $orderBy = 'created_at', $direction = 'desc')
    {
        return $this->findOrFail($id)->campaigns()
            ->with('users', 'owner', 'media')
            ->wherePivotIn('role_id', app(RoleRepository::class)->findRoleOrFail([
                Role::ROLE_OWNER,
                Role::ROLE_MODERATOR,
                Role::ROLE_MEMBER,
            ], Role::TYPE_CAMPAIGN)->pluck('id')->all())
            ->orderBy($orderBy, $direction)
            ->simplePaginate(2);
    }

    public function getTimeline($user)
    {
        $activities = $user->activities()
            ->with('activitiable.media')
            ->whereIn('activitiable_type', [
                Campaign::class,
                Action::class,
                Event::class,
            ])
            ->orderBy('id', 'DESC')
            ->paginate(config('setting.pagination.timeline'));

        return [
            'currentPageUser' => $user,
            'listActivity' => $activities,
        ];
    }

    /**
     * List all campaigns that the user are following through tag
     * @param  int $id
     * @param  string $orderBy
     * @param  string $direction
     * @return Illuminate\Pagination\Paginator
     */
    public function listFollowingCampaign($id, $orderBy = 'created_at', $direction = 'desc')
    {
        return \App\Models\Campaign::with('users', 'owner', 'media')->whereHas('tags', function ($query) use ($id) {
            $query->whereIn('campaign_tag.tag_id', \App\Models\User::findOrFail($id)->tags->pluck('id'));
        })
            ->distinct()
            ->orderBy($orderBy, $direction)
            ->simplePaginate(config('settings.pagination.following_campaign'));
    }

    /**
     * List all tags that the user are following
     * @param  int $id
     * @param  string $orderBy
     * @param  string $direction
     * @return Illuminate\Pagination\Paginator
     */
    public function listFollowingTag($id, $orderBy = 'created_at', $direction = 'desc')
    {
        $user = $this->findOrFail($id);

        return $user
            ->tags()
            ->orderBy($orderBy, $direction)
            ->simplePaginate();
    }

    /**
     * User join or quit campaign
     * @param  int $campaignId
     */
    public function joinCampaign($campaignId)
    {
        \Auth::guard('api')
            ->user()
            ->campaigns()
            ->toggle([$campaignId => ['status' => Campaign::APPROVING]]);
    }

    /**
     * Multiupload images
     * @param  array  $files
     * @param  string $path
     */
    public function uploadImages(array $files, $path)
    {
        foreach ($files as $key => $file) {
            $uploadedFiles[$key]['url_file'] = $this->uploadFile($file, $path);
            $uploadedFiles[$key]['type'] = \App\Models\Media::IMAGE;
        }

        return \Auth::guard('api')
            ->user()
            ->media()
            ->createMany($uploadedFiles);
    }

    /**
     * search members of campaign
     * @param  App\Models\Campaign $campaign
     * @param  int $id
     * @return boolen
     */
    public function searchMembers($campaignId, $status, $search, $roleId)
    {
        $user = $this;

        if (!is_null($search)) {
            $user = $user->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }

        return $user->where('status', User::ACTIVE)
            ->whereHas('campaigns', function ($query) use ($campaignId, $status, $roleId) {
            $query = $query->where('campaign_id', $campaignId)
                ->where('campaign_user.status', $status);

            if ($roleId != config('settings.search_default')) {
                $query = $query->where('campaign_user.role_id', $roleId);
            }
        })->with(['campaigns' => function ($query) use ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }])->paginate(config('settings.paginate_default'));
    }
}
