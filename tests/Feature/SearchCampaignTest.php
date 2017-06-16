<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Tag;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchCampaignTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * test search campaign success.
     *
     * @return void
     */
    public function testSearchCampaignWithAllDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'tag_id' => [
                0 => $tag->id,
            ],
            'hashtag' => 'Default',
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'date_start' => '12/12/2016',
            'date_end' => '12/05/2017',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that tag_id have data then success.
     *
     * @return void
     */
    public function testSearchCampaignWithTagIdHaveDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'tag_id' => [
                0 => $tag->id,
            ],
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that title have data then success.
     *
     * @return void
     */
    public function testSearchCampaignWithTitleHaveDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'title' => 'Campaign Test',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that hastag have data then success.
     *
     * @return void
     */
    public function testSearchCampaignWithHastagHaveDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'hashtag' => 'Default',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that address have data then success.
     *
     * @return void
     */
    public function testSearchCampaignWithAddressHaveDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'latitude' => 16.054407,
            'longitude' => 108.202167,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that date_start and date_end have data then success.
     *
     * @return void
     */
    public function testSearchCampaignWithDateHaveDataThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'date_start' => '12/12/2016',
            'date_end' => '12/05/2017',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test search campaign that have mutiple data then success.
     *
     * @return void
     */
    public function testSearchCampaignWitResultMutiplehDateThenSuccess()
    {
        $user = factory(User::class)->create();
        $tag = factory(Tag::class)->create([
            'name' => 'Tag demo',
        ]);

        $campaign = factory(Campaign::class)->create([
            'title' => 'Campaign Test',
            'latitude' => 16.054407,
            'longitude' => 108.202167,
            'hashtag' => 'Default',
            'status' => Campaign::STATUS_PUBLIC,
        ]);

        $campaign->settings()->create([
            [
                'key' => config('settings.campaign.start_day'),
                'value' => '12/10/2016',
            ], [
                'key' => config('settings.campaign.end_day'),
                'value' => '12/10/2017',
            ],
        ]);

        $campaign->tags()->attach($tag->id);
        $this->actingAs($user, 'api');

        $response = $this->json('GET', route('campaign.search'), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }
}
