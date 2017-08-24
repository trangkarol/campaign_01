<?php

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Contracts\TagInterface;
use Carbon\Carbon;

class TagRepository extends BaseRepository implements TagInterface
{
    public function model()
    {
        return Tag::class;
    }

    public function getOrCreate($tags)
    {
        if (!is_array($tags)) {
            return false;
        }

        $newTags = [];
        $oldTags = [];

        foreach ($tags as $tag) {
            if (empty($tag['id'])) {
                $newTags[] = ['name' => strtolower($tag['name'])];
            } else {
                $oldTags[] = $tag['id'];
            }
        }

        return [
            'old' => $oldTags,
            'new' => $newTags,
        ];
    }

    public function deleteFromCampaign($campaign)
    {
        if (!is_null($campaign)) {
            $currentDay = Carbon::Now();
            $campaign->tags()->get()->each(function ($tag) use ($campaign, $currentDay) {
                $tag->campaigns()->updateExistingPivot($campaign->id, ['deleted_at' => $currentDay]);
            });

            return true;
        }

        return false;
    }

    public function openFromCampaign($campaign)
    {
        if (!is_null($campaign)) {
            $campaign->tags()->get()->each(function ($tag) use ($campaign) {
                $tag->campaigns()->updateExistingPivot($campaign->id, ['deleted_at' => null]);
            });

            return true;
        }

        return false;
    }
}
