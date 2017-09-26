<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repositories\Eloquent\RoleRepository;

class ManageCampaignTest extends TestCase
{
    use DatabaseTransactions;

    public function testChangeRoleWithUnauthorizedThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $owner = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $owner->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('campaign.change-role'), [
            'userId' => $user->id,
            'campaignId' => $campaign->id,
            'roleId' => 4,
        ]);

        $response->assertStatus(UNAUTHORIZED);
    }

    public function testChangeRoleWithInvalidDataThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('campaign.change-role'), [
            'userId' => 123,
            'campaignId' => $campaign->id,
            'roleId' => 4,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)
            ->assertExactJson([
                'http_status' => [
                    'code' => INTERNAL_SERVER_ERROR,
                    'message' => 'Invalid data input',
                    'status' => false,
                ],
            ]);
    }

    public function testChangeRoleThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $member = factory(User::class)->create();
        $owner = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $memberId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $owner->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
            $member->id => [
                'role_id' => $memberId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($owner, 'api');
        $response = $this->json('PATCH', route('campaign.change-role'), [
            'userId' => $member->id,
            'campaignId' => $campaign->id,
            'roleId' => 6,
        ]);

        $response->assertStatus(CODE_OK);
    }

    public function testRemoveUserWithUnauthorizedThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $owner = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(\App\Repositories\Eloquent\RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $owner->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('campaign.remove-user'), [
            'userId' => 1,
            'campaignId' => $campaign->id,
        ]);

        $response->assertExactJson([
                'http_status' => [
                    'code' => INTERNAL_SERVER_ERROR,
                    'message' => 'This action is unauthorized.',
                    'status' => false,
                ],
            ]);
    }

    public function testRemoveUserThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $member = factory(User::class)->create();
        $owner = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $memberId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $owner->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
            $member->id => [
                'role_id' => $memberId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($owner, 'api');
        $response = $this->json('PATCH', route('campaign.remove-user'), [
            'userId' => $member->id,
            'campaignId' => $campaign->id,
        ]);

        $response->assertStatus(CODE_OK);
    }

    public function testChangeOwnerThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $member = factory(User::class)->create();
        $owner = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $ownerId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_OWNER, Role::TYPE_CAMPAIGN)
            ->id;
        $memberId = app(RoleRepository::class)
            ->findRoleOrFail(Role::ROLE_MEMBER, Role::TYPE_CAMPAIGN)
            ->id;
        $campaign->users()->attach([
            $owner->id => [
                'role_id' => $ownerId,
                'status' => CAMPAIGN::APPROVED,
            ],
            $member->id => [
                'role_id' => $memberId,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $this->actingAs($owner, 'api');
        $response = $this->json('PATCH', route('campaign.change-owner'), [
            'userId' => $member->id,
            'campaignId' => $campaign->id,
            'roleId' => 6,
        ]);

        $response->assertStatus(CODE_OK);
    }
}
