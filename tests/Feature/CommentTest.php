<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Campaign;
use App\Models\Event;
use App\Models\Action;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommentTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateCommentEventWithSuperAdminThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $event = factory(Event::class)->create();
        $roleAdminId = Role::where('type', Role::TYPE_SYSTEM)->pluck('id')->first();
        $user->roles()->attach($roleAdminId);
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('comment.create', ['modelId' => $event->id, 'parentId' => 0, 'flag' => 'event']), [
            'content' => $faker->sentence(10),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertJson([
            'createComment' => true,
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testCreateCommentEventWithOwnerThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create(['status' => 1]);
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->json('POST', route('comment.create', ['modelId' => $event->id, 'parentId' => 0, 'flag' => 'event']), [
            'content' => $faker->sentence(10),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertJson([
            'createComment' => true,
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testCreateCommentEventWithoutContentThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->json('POST', route('comment.create', ['modelId' => $event->id, 'parentId' => 0, 'flag' => 'event']), [
            'content' => '',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR);
    }

    public function testCreateCommentWithoutMemberOfCampaignThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->json('POST', route('comment.create', ['modelId' => $event->id, 'parentId' => 0, 'flag' => 'event']), [
            'content' => $faker->sentence(10),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR);
    }

    public function testUpdateCommentWithOwnerThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create(['status' => 1]);
        $this->actingAs($user, 'api');
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $comment = factory(Comment::class)->make([
            'user_id' => $user->id,
        ])->toArray();

        $commented = $event->comments()->create($comment);

        $response = $this->json('POST', route('comment.update', ['comment' => $commented->id, 'flag' => 'event']), [
            'content' => $faker->sentence(10),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    public function testUpdateCommentWithoutOwnerThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create(['status' => 1]);
        $user2 = factory(User::class)->create(['status' => 1]);
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $user2->roles()->attach([Role::ROLE_MEMBER]);
        $this->actingAs($user, 'api');
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
            $user2->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $comment = factory(Comment::class)->make([
            'user_id' => $user2->id,
        ])->toArray();

        $commented = $event->comments()->create($comment);

        $response = $this->json('POST', route('comment.update', ['comment' => $commented->id, 'flag' => 'event']), [
            'content' => $faker->sentence(10),
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR);
    }

    public function testDeleteCommentWithOwnerThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create(['status' => 1]);
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $this->actingAs($user, 'api');
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $comment = factory(Comment::class)->make([
            'user_id' => $user->id,
        ])->toArray();

        $commented = $event->comments()->create($comment);

        $response = $this->json('DELETE', route('comment.destroy', ['comment' => $commented->id]), [
            'modelId' => $event->id,
            'flag' => 'event',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK);
    }

    public function testDeleteCommentWithoutOwnerThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create(['status' => 1]);
        $user2 = factory(User::class)->create(['status' => 1]);
        $user->roles()->attach([Role::ROLE_MEMBER]);
        $user2->roles()->attach([Role::ROLE_MEMBER]);
        $this->actingAs($user, 'api');
        $roleCampaign = Role::where('name', Role::ROLE_MEMBER)->first();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Heath socical',
        ]);

        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
            $user2->id => [
                'role_id' => $roleCampaign->id,
                'status' => Campaign::APPROVED,
            ],
        ]);

        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $comment = factory(Comment::class)->make([
            'user_id' => $user2->id,
        ])->toArray();

        $commented = $event->comments()->create($comment);

        $response = $this->json('DELETE', route('comment.destroy', ['comment' => $commented->id]), [
            'modelId' => $event->id,
            'flag' => 'event',
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR);
    }
}
