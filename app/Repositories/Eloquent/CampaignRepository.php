<?php

namespace App\Repositories\Eloquent;

use App\Models\Media;
use App\Models\Campaign;
use App\Traits\Common\UploadableTrait;
use App\Repositories\Contracts\CampaignInterface;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknowException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CampaignRepository extends BaseRepository implements CampaignInterface
{
    use UploadableTrait;

    public function model()
    {
        return Campaign::class;
    }

    private function isArraySettings($settings)
    {
        if (!$settings) {
            return false;
        } elseif (!is_array($settings)) {
            throw new UnknowException('Settings is not array');
        }

        //check each element is array
        foreach ($settings as $setting) {
            if (!is_array($setting)) {
                throw new UnknowException('Invalit format settings array');
            }
        }

        return true;
    }

    public function create($inputs)
    {
        if (is_null($inputs) || !is_array($inputs)) {
            throw new UnknowException('Inputs is null or not an array');
        }

        $data = [
            'title' => $inputs['title'],
            'hashtag' => $inputs['hashtag'],
            'description' => $inputs['description'],
            'longitude' => $inputs['longitude'],
            'latitude' => $inputs['latitude'],
            'status' => Campaign::ACTIVE,
        ];

        $campaign = parent::create($data);

        if (!$campaign) {
            throw new NotFoundException('Error create campaign');
        }

        if (array_key_exists('settings', $inputs) && $this->isArraySettings($inputs['settings'])) {
            $campaign->settings()->createMany($inputs['settings']);
        }

        if (!empty($inputs['media']) && is_file($inputs['media'])) {
            $urlFile = $this->uploadFile($inputs['media'], 'campaigns');
            $campaign->media()->create([
                'url_file' => $urlFile,
                'type' => Media::IMAGE,
            ]);
        }

        $campaign->users()->attach($inputs['user_id'], [
            'role_id' => $inputs['role_id'],
        ]);

        if ($inputs['tags'] && is_array($inputs['tags'])) {
            $campaign->tags()->attach($inputs['tags']['old']);
            $campaign->tags()->createMany($inputs['tags']['new']);
        }

        return true;
    }

    public function delete($campaign)
    {
        $campaign->donations()->delete();
        $campaign->tags()->detach();
        $campaign->users()->detach();
        $campaign->media()->delete();
        $campaign->likes()->delete();
        $campaign->settings()->delete();

        return $campaign->delete();
    }

    public function search($inputs)
    {
        $tagIds = $inputs['tag_id'];
        $dateStart = $inputs['date_start'];
        $dateEnd = $inputs['date_end'];
        $campaign = $this;

        if ($inputs['status']) {
            $campaign = $campaign->where('status', $inputs['status']);
        }

        if ($inputs['hashtag']) {
            $campaign = $campaign->where('hashtag', 'like', '%' . $inputs['hashtag'] . '%');
        }

        if ($inputs['title']) {
            $campaign = $campaign->where('title', 'like', '%' . $inputs['title'] . '%');
        }

        if ($inputs['latitude']) {
            $campaign = $campaign->where('latitude', '>=', $inputs['latitude']);
        }

        if ($inputs['longitude']) {
            $campaign = $campaign->where('longitude', '<=', $inputs['longitude']);
        }

        if ($tagIds != config('settings.search_default')) {
            $campaign = $campaign->with(['tags' => function ($query) use ($tagIds) {
                $query->whereIn('campaign_tag.tag_id', $tagIds);
            }]);
        }

        if ($dateStart) {
            $campaign = $campaign->with(['settings' => function ($query) use ($dateStart) {
                $query->where('settings.key', config('settings.campaign.start_day'))->where('value', '>=', $dateStart);
            }]);
        }

        if ($dateEnd) {
            $campaign = $campaign->with(['settings' => function ($query) use ($dateEnd) {
                $query->where('settings.key', config('settings.campaign.end_day'))->where('value', '<=', $dateEnd);
            }]);
        }

        return $campaign->get();// bo sung phan trang khi campaign timeline duoc merge
    }
}
