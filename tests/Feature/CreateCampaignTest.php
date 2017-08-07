<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateCampaign extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateCampaignSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => $faker->unique()->name,
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'address' => $faker->address,
            'tags' => [
                [
                    'id' => null,
                    'name' => 'php',
                ],
                [
                    'id' => null,
                    'name' => 'laravel',
                ],
                [
                    'id' => null,
                    'name' => 'laravel',
                ],
                [
                    'id' => null,
                    'name' => 'vue.js',
                ],
                [
                    'id' => null,
                    'name' => 'mysql',
                ],
            ],
            'settings' => [
                [
                    'key' => 1,
                    'value' => '1',
                ],
                [
                    'key' => 2,
                    'value' => '5',
                ],
                [
                    'key' => 3,
                    'value' => null,
                ],
            ],
            'media' => UploadedFile::fake()->image('avatar.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertJson([
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testCreateCampaignWithNullTagThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => $faker->unique()->name,
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'tags' => [],
            'address' => $faker->address,
            'settings' => [
                [
                    'key' => 1,
                    'value' => '0',
                ],
                [
                    'key' => 2,
                    'value' => '10',
                ],
            ],
            'media' => UploadedFile::fake()->image('avatar1.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertJson([
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testCreateCampaignWithNullSettingThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => $faker->unique()->name,
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'address' => $faker->address,
            'tags' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => null,
                    'name' => 'demo',
                ],
            ],
            'settings' => [],
            'media' => UploadedFile::fake()->image('avatar2.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertJson([
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testCreateCampaignWithDuplicateHashTagThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        factory(Campaign::class)->create(['hashtag' => 'duplicate']);
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => 'duplicate',
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'address' => $faker->address,
            'tags' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => null,
                    'name' => 'demo',
                ],
            ],
            'settings' => [
                'key' => 1,
                'value' => '0',
            ],
            'media' => UploadedFile::fake()->image('avatar2.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR)->assertJson([
            'code' => VALIDATOR_ERROR,
            'status' => false,
            'messages' => [
                trans('validation.unique', ['attribute' => 'hashtag']),
            ],
        ]);
    }

    public function testCreateCampaignWithFiledsNullThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => null,
            'description' => null,
            'hashtag' => null,
            'longitude' => null,
            'latitude' => null,
            'tags' => null,
            'settings' => null,
            'address' => $faker->address,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR)->assertJson([
            'code' => VALIDATOR_ERROR,
            'status' => false,
            'messages' => [
                trans('validation.required', ['attribute' => 'title']),
                trans('validation.required', ['attribute' => 'description']),
                trans('validation.required', ['attribute' => 'hashtag']),
                trans('validation.required', ['attribute' => 'media']),
            ],
        ]);
    }

    public function testCreateCampaignWithInvalidSettingThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => $faker->unique()->name,
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'tags' => [],
            'address' => $faker->address,
            'settings' => [
                [
                    'key' => 3,
                    'value' => null,
                ],
                'key' => 1,
                'value' => '1',
            ],
            'media' => UploadedFile::fake()->image('avatar2.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)->assertExactJson([
            'http_status' => [
                'status' => false,
                'code' => INTERNAL_SERVER_ERROR,
                'message' => 'Invalid format array',
        ]]);
    }

    public function testCreateCampaignWithInvalidLongitudeLatitudeThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.store'), [
            'title' => $faker->name,
            'description' => $faker->paragraph(),
            'hashtag' => $faker->unique()->name,
            'longitude' => 181,
            'latitude' => -91,
            'tags' => [],
            'address' => $faker->address,
            'settings' => [
                [
                    'key' => 3,
                    'value' => null,
                ],
                'key' => 1,
                'value' => '1',
            ],
            'media' => UploadedFile::fake()->image('avatar2.jpg', 600, 600),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR)->assertExactJson([
            'code' => VALIDATOR_ERROR,
            'status' => false,
            'messages' => [
                trans('validation.max.numeric', ['attribute' => 'longitude', 'max' => 180]),
                trans('validation.min.numeric', ['attribute' => 'latitude', 'min' => -90]),
            ],
        ]);
    }
}
