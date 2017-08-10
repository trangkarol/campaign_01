<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Campaign;
use App\Models\Event;
use App\Models\Action;
use App\Models\Comment;
use App\Models\Donation;
use App\Models\User;
use App\Models\Activity;
use App\Policies\CampaignPolicy;
use App\Policies\EventPolicy;
use App\Policies\ActionPolicy;
use App\Policies\DonationPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Event::class => EventPolicy::class,
        Action::class => ActionPolicy::class,
        Comment::class => CommentPolicy::class,
        Donation::class => DonationPolicy::class,
        Activity::class => ActivityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
    }
}
