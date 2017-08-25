<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected static $repositories = [
        'user' => [
            \App\Repositories\Contracts\UserInterface::class,
            \App\Repositories\Eloquent\UserRepository::class,
        ],
        'role' => [
            \App\Repositories\Contracts\RoleInterface::class,
            \App\Repositories\Eloquent\RoleRepository::class,
        ],
        'campaign' => [
            \App\Repositories\Contracts\CampaignInterface::class,
            \App\Repositories\Eloquent\CampaignRepository::class,
        ],
        'tag' => [
            \App\Repositories\Contracts\TagInterface::class,
            \App\Repositories\Eloquent\TagRepository::class,
        ],
        'event' => [
            \App\Repositories\Contracts\EventInterface::class,
            \App\Repositories\Eloquent\EventRepository::class,
        ],
        'quality' => [
            \App\Repositories\Contracts\QualityInterface::class,
            \App\Repositories\Eloquent\QualityRepository::class,
        ],
        'social_account' => [
            \App\Repositories\Contracts\SocialAccountInterface::class,
            \App\Repositories\Eloquent\SocialAccountRepository::class,
        ],
        'action' => [
            \App\Repositories\Contracts\ActionInterface::class,
            \App\Repositories\Eloquent\ActionRepository::class,
        ],
        'media' => [
            \App\Repositories\Contracts\MediaInterface::class,
            \App\Repositories\Eloquent\MediaRepository::class,
        ],
        'comment' => [
            \App\Repositories\Contracts\CommentInterface::class,
            \App\Repositories\Eloquent\CommentRepository::class,
        ],
        'donation' => [
            \App\Repositories\Contracts\DonationInterface::class,
            \App\Repositories\Eloquent\DonationRepository::class,
        ],
        'donation_type' => [
            \App\Repositories\Contracts\DonationTypeInterface::class,
            \App\Repositories\Eloquent\DonationTypeRepository::class,
        ],
        'like' => [
            \App\Repositories\Contracts\LikeInterface::class,
            \App\Repositories\Eloquent\LikeRepository::class,
        ],
        'expense' => [
            \App\Repositories\Contracts\ExpenseInterface::class,
            \App\Repositories\Eloquent\ExpenseRepository::class,
        ],
        'product' => [
            \App\Repositories\Contracts\ProductInterface::class,
            \App\Repositories\Eloquent\ProductRepository::class,
        ],
        'goal' => [
            \App\Repositories\Contracts\GoalInterface::class,
            \App\Repositories\Eloquent\GoalRepository::class,
        ],
        'activity' => [
            \App\Repositories\Contracts\ActivityInterface::class,
            \App\Repositories\Eloquent\ActivityRepository::class,
        ],
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (static::$repositories as $repository) {
            $this->app->singleton(
                $repository[0],
                $repository[1]
            );
        }
    }
}
