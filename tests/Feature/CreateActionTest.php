<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\Action;
use App\Models\Campaign;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * test create action success.
     *
     * @return void
     */
    // public function testCreateActionThenSuccess()
    // {
    //     $faker = \Faker\Factory::create();
    //     $user = factory(User::class)->create([
    //         'status' => User::ACTIVE,
    //     ]);
    //     $campaign = factory(Campaign::class)->create([
    //         'hashtag' => 'Default',
    //         'status' => Campaign::ACTIVE,
    //     ]);

    //     $campaign->settings()->create([
    //         'key' => config('settings.campaigns.status'),
    //         'value' => config('settings.value_of_settings.status.public'),
    //     ]);

    //     $roleCampaigns = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
    //     $campaign->users()->attach([
    //         $user->id => [
    //             'role_id' => $roleCampaigns[Role::ROLE_MEMBER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //         '1' => [
    //             'role_id' => $roleCampaigns[Role::ROLE_OWNER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //     ]);

    //     $campaign->events()->create([
    //         'title' => $faker->sentence(10),
    //         'description' => $faker->paragraph(),
    //         'longitude' => $faker->longitude,
    //         'latitude' => $faker->latitude,
    //         'user_id' => $user->id,
    //     ]);

    //     $eventId = $campaign->events()->pluck('id')->first();
    //     $this->actingAs($user, 'api');

    //     $response = $this->json('POST', route('action.create'), [
    //         'user_id' => $user->id,
    //         'event_id' => $eventId,
    //         'caption' => $faker->sentence(3),
    //         'description' => $faker->paragraph(),
    //         'upload' => [
    //             'image' => [
    //                 UploadedFile::fake()->image('avatar.jpg', 600, 600),
    //             ],
    //             'video' => [
    //                 'https://www.youtube.com/watch?v=pUSIzYWm_ew',
    //             ],
    //         ],
    //     ], [
    //         'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
    //     ]);

    //     $response->assertStatus(CODE_OK);
    // }

    /**
     * test create action with file null success.
     *
     * @return void
     */
    // public function testCreateActionWithFilesNullThenSuccess()
    // {
    //     $faker = \Faker\Factory::create();
    //     $user = factory(User::class)->create([
    //         'status' => User::ACTIVE,
    //     ]);
    //     $campaign = factory(Campaign::class)->create([
    //         'hashtag' => 'Default',
    //         'status' => Campaign::ACTIVE,
    //     ]);

    //     $campaign->settings()->create([
    //         'key' => config('settings.campaigns.status'),
    //         'value' => config('settings.value_of_settings.status.public'),
    //     ]);

    //     $roleCampaigns = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
    //     $campaign->users()->attach([
    //         $user->id => [
    //             'role_id' => $roleCampaigns[Role::ROLE_MEMBER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //         '1' => [
    //             'role_id' => $roleCampaigns[Role::ROLE_OWNER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //     ]);

    //     $campaign->events()->create([
    //         'title' => $faker->sentence(10),
    //         'description' => $faker->paragraph(),
    //         'longitude' => $faker->longitude,
    //         'latitude' => $faker->latitude,
    //         'user_id' => $user->id,
    //     ]);

    //     $eventId = $campaign->events()->pluck('id')->first();
    //     $this->actingAs($user, 'api');

    //     $response = $this->json('POST', route('action.create'), [
    //         'user_id' => $user->id,
    //         'event_id' => $eventId,
    //         'caption' => $faker->sentence(3),
    //         'description' => $faker->paragraph(),
    //     ], [
    //         'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
    //     ]);

    //     $response->assertStatus(CODE_OK);
    // }

    /**
     * test create action with caption null success.
     *
     * @return void
     */
    public function testCreateActionWithCaptionNullThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create([
            'status' => User::ACTIVE,
        ]);
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Default',
            'status' => Campaign::ACTIVE,
        ]);

        $campaign->settings()->create([
            'key' => config('settings.campaigns.status'),
            'value' => config('settings.value_of_settings.status.public'),
        ]);

        $roleCampaigns = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaigns[Role::ROLE_MEMBER],
                'status' => Campaign::APPROVED,
            ],
            '1' => [
                'role_id' => $roleCampaigns[Role::ROLE_OWNER],
                'status' => Campaign::APPROVED,
            ],
        ]);

        $campaign->events()->create([
            'title' => $faker->sentence(10),
            'description' => $faker->paragraph(),
            'longitude' => $faker->longitude,
            'latitude' => $faker->latitude,
            'user_id' => '1',
        ]);

        $eventId = $campaign->events()->pluck('id')->first();
        $this->actingAs($user, 'api');

        $response = $this->json('POST', route('action.create'), [
            'user_id' => $user->id,
            'event_id' => $eventId,
            'description' => $faker->paragraph(),
            'upload' => [
                'image' => [
                    UploadedFile::fake()->image('avatar.jpg', 600, 600),
                ],
                'video' => [
                    'https://www.youtube.com/watch?v=pUSIzYWm_ew',
                ],
            ],
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR)->assertJson([
            'code' => VALIDATOR_ERROR,
            'status' => false,
            'messages' => [
                trans('validation.required', ['attribute' => 'caption']),
            ],
        ]);
    }

    /**
     * test create action with description null success.
     *
     * @return void
    */
    // public function testCreateActionWithDescriptionNullThenFail()
    // {
    //     $faker = \Faker\Factory::create();
    //     $user = factory(User::class)->create([
    //         'status' => User::ACTIVE,
    //     ]);
    //     $campaign = factory(Campaign::class)->create([
    //         'hashtag' => 'Default',
    //         'status' => Campaign::ACTIVE,
    //     ]);

    //     $campaign->settings()->create([
    //         'key' => config('settings.campaigns.status'),
    //         'value' => config('settings.value_of_settings.status.public'),
    //     ]);

    //     $roleCampaigns = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
    //     $campaign->users()->attach([
    //         $user->id => [
    //             'role_id' => $roleCampaigns[Role::ROLE_MEMBER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //         '1' => [
    //             'role_id' => $roleCampaigns[Role::ROLE_OWNER],
    //             'status' => Campaign::APPROVED,
    //         ],
    //     ]);

    //     $campaign->events()->create([
    //         'title' => $faker->sentence(10),
    //         'description' => $faker->paragraph(),
    //         'longitude' => $faker->longitude,
    //         'latitude' => $faker->latitude,
    //         'user_id' => '1',
    //     ]);

    //     $eventId = $campaign->events()->pluck('id')->first();
    //     $this->actingAs($user, 'api');

    //     $response = $this->json('POST', route('action.create'), [
    //         'user_id' => $user->id,
    //         'event_id' => $eventId,
    //         'caption' => $faker->sentence(3),
    //         'upload' => [
    //             'image' => [
    //                 UploadedFile::fake()->image('avatar.jpg', 600, 600),
    //             ],
    //             'video' => [
    //                 'https://www.youtube.com/watch?v=pUSIzYWm_ew',
    //             ],
    //         ],
    //     ], [
    //         'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
    //     ]);

    //     $response->assertStatus(CODE_OK);
    // }

    /**
     * test create action with description null success.
     *
     * @return void
    */
    public function testCreateActionWithAuthorizedhenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create([
            'status' => User::ACTIVE,
        ]);
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Default',
            'status' => Campaign::ACTIVE,
        ]);
        $roleCampaigns = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
        $campaign->users()->attach([
            '1' => [
                'role_id' => $roleCampaigns[Role::ROLE_OWNER],
                'status' => Campaign::APPROVED,
            ],
        ]);

        $campaign->events()->create([
            'title' => $faker->sentence(10),
            'description' => $faker->paragraph(),
            'longitude' => $faker->longitude,
            'latitude' => $faker->latitude,
            'user_id' => '1',
        ]);

        $eventId = $campaign->events()->pluck('id')->first();
        $this->actingAs($user, 'api');

        $response = $this->json('POST', route('action.create'), [
            'user_id' => $user->id,
            'event_id' => $eventId,
            'caption' => $faker->sentence(3),
            'description' => $faker->paragraph(),
            'upload' => [
                'image' => [
                    UploadedFile::fake()->image('avatar.jpg', 600, 600),
                ],
                'video' => [
                    'https://www.youtube.com/watch?v=pUSIzYWm_ew',
                ],
            ],
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(UNAUTHORIZED);
    }
}
