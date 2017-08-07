<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ChangeStatusUserTest extends TestCase
{
    use DatabaseTransactions;

    /**
      * test approve user join campaign with moderator success.
      *
      * @return void
    */
    public function testApproveUserJoinCampaignWithModeratorThenSuccess()
    {
        $user = factory(User::class)->create();
        $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->toArray();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $campaign->users()->attach([
            '1' => [
                'status' => Campaign::APPROVING,
                'role_id' => $roleCampaignIds[Role::ROLE_MEMBER],
            ],
            $user->id => [
                'status' => Campaign::APPROVED,
                'role_id' => $roleCampaignIds[Role::ROLE_MODERATOR],
            ],
        ]);

        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.approve', [
                'campaignId' => $campaign->id,
                'userId'=> 1,
                'flag' => Campaign::FLAG_APPROVE,
            ]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
            ]);

        $response->assertStatus(CODE_OK);
    }

    /**
     * test approve user join campaign with owner success.
      *
      * @return void
    */
    public function testApproveUserJoinCampaignWithOwnerThenSuccess()
    {
        $user = factory(User::class)->create();
        $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->toArray();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $campaign->users()->attach([
            '1' => [
                'status' => Campaign::APPROVING,
                'role_id' => $roleCampaignIds[Role::ROLE_MEMBER],
            ],
            $user->id => [
                'status' => Campaign::APPROVED,
                'role_id' => $roleCampaignIds[Role::ROLE_OWNER],
            ],
        ]);

        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.approve', [
                'campaignId' => $campaign->id,
                'userId'=> 1,
                'flag' => Campaign::FLAG_APPROVE,
            ]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
            ]);

        $response->assertStatus(CODE_OK);
    }

    /**
      * test approve user join campaign with fail.
      *
      * @return void
    */
    public function testAprroveUserWithCampaignNotFoundThenFail()
    {
        $user = factory(User::class)->create();
        $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->toArray();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $campaign->users()->attach([
            '1' => [
                'status' => Campaign::APPROVING,
                'role_id' => $roleCampaignIds[Role::ROLE_MEMBER],
            ],
            $user->id => [
                'status' => Campaign::APPROVED,
                'role_id' => $roleCampaignIds[Role::ROLE_OWNER],
            ],
        ]);

        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.approve', [
                'campaignId' => -1,
                'userId'=> 1,
                'flag' => Campaign::FLAG_APPROVE,
            ]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
            ]);

        $response->assertStatus(NOT_FOUND);
    }

    /**
      * test approve user join campaign with fail.
      *
      * @return void
    */
    public function testApproveUserThenFail()
    {
        $user = factory(User::class)->create();
        $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->toArray();
        $campaign = factory(Campaign::class)->create(['hashtag' => 'Default']);
        $campaign->users()->attach([
            '1' => [
                'status' => Campaign::APPROVING,
                'role_id' => $roleCampaignIds[Role::ROLE_OWNER],
            ],
            $user->id => [
                'status' => Campaign::APPROVED,
                'role_id' => $roleCampaignIds[Role::ROLE_MEMBER],
            ],
        ]);

        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.approve', [
                'campaignId' => $campaign->id,
                'userId'=> 1,
                'flag' => Campaign::FLAG_APPROVE,
            ]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
            ]);

        $response->assertStatus(UNAUTHORIZED);
    }
}
