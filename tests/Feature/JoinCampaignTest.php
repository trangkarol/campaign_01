<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class JoinCampaignTest extends TestCase
{
    use DatabaseTransactions;

    /**
      * test user join campaign with authozication then success.
      *
      * @return void
    */
    // public function testUserJoinCampaignWithAthozicationThenSuccess()
    // {
    //     $user = factory(User::class)->create();
    //     $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
    //     $campaign = factory(Campaign::class)->create([
    //         'hashtag' => 'Default',
    //         'status' => Campaign::ACTIVE,
    //     ]);

    //     $campaign->users()->attach([
    //         '1' => [
    //             'status' => Campaign::APPROVED,
    //             'role_id' => $roleCampaignIds[Role::ROLE_OWNER],
    //         ],
    //     ]);

    //     $this->actingAs($user, 'api');
    //     $response = $this->json('POST', route('campaign.attendCampaign', ['id' => $campaign->id, 'flag' => 'join']), [
    //         'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
    //     ]);

    //     $response->assertStatus(CODE_OK);
    // }

    /**
      * test user join campaign with authozication then fail.
      *
      * @return void
    */
    public function testUserJoinCampaignWithAthozicationThenFail()
    {
        $user = factory(User::class)->create();
        $roleCampaignIds = Role::where('type', Role::TYPE_CAMPAIGN)->pluck('id', 'name')->all();
        $campaign = factory(Campaign::class)->create([
            'hashtag' => 'Default',
            'status' => Campaign::BLOCK,
        ]);

        $campaign->users()->attach([
            '1' => [
                'status' => Campaign::APPROVED,
                'role_id' => $roleCampaignIds[Role::ROLE_OWNER],
            ],
        ]);

        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('campaign.attendCampaign', ['id' => $campaign->id, 'flag' => 'join']), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(UNAUTHORIZED);
    }
}
