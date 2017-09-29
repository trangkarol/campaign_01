<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateDonationTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateDonationWithOwnerCampaignThenSuccess()
    {
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
        ]);

        $campaign->settings()->create([
            'key' => config('settings.campaigns.status'),
            'value' => config('settings.value_of_settings.status.public'),
        ]);

        $roleCampaign = Role::where('name', Role::ROLE_OWNER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('donation.store'), [
            'event_id' => $event->id,
            'goal_id' => 3,
            'value' => rand(10, 1000),
            'status' => 0,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $data = $response->getdata()->donation;

        $response->assertStatus(CODE_OK);
    }

    public function testCreateEventWithAdminThenSuccess()
    {
        $user = User::find(1);
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('donation.store'), [
            'event_id' => 3,
            'goal_id' => 3,
            'value' => rand(10, 1000),
            'status' => 0,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);
        $data = $response->getdata()->donation;

        $response->assertStatus(CODE_OK);
    }

    public function testCreateDonationbutNotOwnerThenFail()
    {
        $user = factory(User::class)->create([
            'status' => CAMPAIGN::APPROVED,
        ]);
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
        ]);
        $event = factory(Event::class)->create([
            'campaign_id' => $campaign->id,
            'user_id' => rand(1, 10),
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('donation.store'), [
            'event_id' => $event->id,
            'goal_id' => 3,
            'value' => rand(10, 1000),
            'user_id' => $user->id,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)->assertJsonFragment([
            'http_status' => [
                'code' => INTERNAL_SERVER_ERROR,
                'status' => false,
                'message' => 'Permission error: User can not donate.',
            ],
        ]);
    }

    public function testCreateDonationtWithValueNullThenFail()
    {
        $user = User::find(1);
        $this->actingAs($user, 'api');
        $response = $this->json('POST', route('donation.store'), [
            'event_id' => 3,
            'goal_id' => 3,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(VALIDATOR_ERROR)->assertExactJson([
            'code' => VALIDATOR_ERROR,
            'status' => false,
            'messages' => [
                trans('validation.required', ['attribute' => 'value']),
            ],
        ]);
    }

    public function testAcceptDonationThenSuccess()
    {
        // $user = User::find(1);
        // $this->actingAs($user, 'api');
        // $response = $this->json('PATCH', route('update-status', ['id' => 1]), [
        //     'status' => 0,
        // ], [
        //     'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        // ]);
        // // $data = $response->getdata()->donation;

        // $response->assertStatus(CODE_OK);
    }

    public function testAcceptDonationButNotOwnerThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create([
            'status' => CAMPAIGN::APPROVED,
        ]);
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
            'hashtag' => $faker->unique()->name,
        ]);
        $roleCampaign = Role::where('name', Role::ROLE_OWNER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $roleMemberCampaign = Role::where('name', Role::ROLE_MEMBER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleMemberCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $campaign->users()->attach([
            3 => [
                'role_id' => $roleCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'user_id' => 3,
            'campaign_id' => $campaign->id,
        ]);
        $donation = factory(Donation::class)->create([
            'user_id' => 3,
            'event_id' => $event->id,
            'campaign_id' => $event->campaign_id,
            'goal_id' => 5,
            'value' => 100,
            'status' => 0,
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('update-status', ['id' => $donation->id]), [
            'status' => CAMPAIGN::APPROVED,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)->assertJsonFragment([
            'http_status' => [
                'code' => INTERNAL_SERVER_ERROR,
                'status' => false,
                'message' => 'Permission error: User can not change this donation.',
            ],
        ]);
    }

    public function testDeleteDoantionThenSuccess()
    {
        $user = User::find(1);
        $this->actingAs($user, 'api');
        $donation = factory(Donation::class)->create();
        $response = $this->json('DELETE', route('donation.destroy', ['id' => $donation->id]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(CODE_OK)->assertExactJson([
            'donation' => 1,
            'http_status' => [
                'code' => CODE_OK,
                'status' => true,
            ],
        ]);
    }

    public function testDeleteDonationButNotOwnerThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
            'hashtag' => $faker->unique()->name,
        ]);
        $roleCampaign = Role::where('name', Role::ROLE_OWNER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $roleMemberCampaign = Role::where('name', Role::ROLE_MEMBER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleMemberCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $campaign->users()->attach([
            3 => [
                'role_id' => $roleCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'user_id' => 3,
            'campaign_id' => $campaign->id,
        ]);
        $donation = factory(Donation::class)->create([
            'user_id' => 3,
            'event_id' => $event->id,
            'campaign_id' => $event->campaign_id,
            'goal_id' => 5,
            'value' => 100,
            'status' => 0,
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('DELETE', route('donation.destroy', ['id' => $donation->id]), [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)->assertJsonFragment([
            'http_status' => [
                'code' => INTERNAL_SERVER_ERROR,
                'status' => false,
                'message' => 'Permission error: User can not delete this donation.',
            ],
        ]);
    }

    public function testUpdateDonationThenSuccess()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
            'hashtag' => $faker->unique()->name,
        ]);
        $roleCampaign = Role::where('name', Role::ROLE_OWNER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $roleMemberCampaign = Role::where('name', Role::ROLE_MEMBER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleMemberCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);
        $donation = factory(Donation::class)->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'campaign_id' => $event->campaign_id,
            'goal_id' => 5,
            'value' => 100,
            'status' => 0,
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('donation.update', ['id' => $donation->id]), [
            'goal_id' => 6,
            'value' => 69,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);
        $data = $response->getdata()->donation;

        $response->assertStatus(CODE_OK);
    }

    public function testUpdateDonationButItWasAcceptedThenFail()
    {
        $faker = \Faker\Factory::create();
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create([
            'status' => Campaign::ACTIVE,
            'hashtag' => $faker->unique()->name,
        ]);
        $roleCampaign = Role::where('name', Role::ROLE_OWNER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $roleMemberCampaign = Role::where('name', Role::ROLE_MEMBER)->where('type', Role::TYPE_CAMPAIGN)->first();
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleMemberCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $campaign->users()->attach([
            $user->id => [
                'role_id' => $roleCampaign->id,
                'status' => CAMPAIGN::APPROVED,
            ],
        ]);
        $event = factory(Event::class)->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
        ]);
        $donation = factory(Donation::class)->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'campaign_id' => $event->campaign_id,
            'goal_id' => 5,
            'value' => 1001,
            'status' => CAMPAIGN::APPROVED,
        ]);
        $this->actingAs($user, 'api');
        $response = $this->json('PATCH', route('donation.update', ['id' => $donation->id]), [
            'goal_id' => 6,
            'value' => 69,
        ], [
            'HTTP_Authorization' => 'Bearer ' . $user->createToken('myToken')->accessToken,
        ]);

        $response->assertStatus(INTERNAL_SERVER_ERROR)->assertJsonFragment([
            'http_status' => [
                'code' => INTERNAL_SERVER_ERROR,
                'status' => false,
                'message' => 'Error: This donation was accepted, you can not edit it.',
            ],
        ]);
    }
}
