<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\User;
use App\Models\Role;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => '123123',
        'remember_token' => str_random(10),
        'birthday' => $faker->date(),
        'address' => $faker->streetAddress(),
        'phone' => $faker->tollFreePhoneNumber(),
        'url_file' => $faker->imageUrl(124, 124, 'fashion', true, 'Faker', false),
        'status' => $faker->randomElement([0, 1]),
        'token_confirm' => str_random(10),
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
        'gender' => $faker->randomElement([0, 1]),
        'head_photo' => '/images/head_photo.jpg',
    ];
});

$factory->define(Role::class, function (Faker\Generator $faker) {
    static $typeId;

    return [
        'name' => $faker->word,
        'type' => $faker->randomElement([1, 2]),
    ];
});

$factory->state(User::class, Role::ROLE_ADMIN, function () {
    return [
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'status' => User::ACTIVE,
    ];
});

$factory->define(App\Models\Campaign::class, function (Faker\Generator $faker) {
    return [
        'hashtag' => str_replace(' ', '', $faker->unique()->name),
        'description' => $faker->paragraph(),
        'longitude' => $faker->longitude,
        'latitude' => $faker->latitude,
        'status' => $faker->randomElement([0, 1]),
        'title' => $faker->paragraph(),
        'address' => $faker->address,
        'number_of_comments' => 10,
        'number_of_likes' => 10,
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(App\Models\Tag::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(App\Models\Event::class, function (Faker\Generator $faker) {
    static $userId;
    static $campaignId;

    return [
        'title' => $faker->sentence(10),
        'description' => $faker->paragraph(),
        'longitude' => $faker->longitude,
        'latitude' => $faker->latitude,
        'address' => $faker->address,
        'number_of_comments' => 10,
        'number_of_likes' => 10,
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(App\Models\Goal::class, function (Faker\Generator $faker) {
    static $eventId;
    static $donationTypeId;

    return [
        'donation_type_id' => $faker
            ->randomElement($donationTypeId ?: $donationTypeId = App\Models\DonationType::pluck('id')->toArray()),
        'event_id' => $faker
            ->randomElement($eventId ?: $eventId = App\Models\Event::pluck('id')->toArray()),
        'goal' => rand(10, 100),
    ];
});

$factory->define(App\Models\Action::class, function (Faker\Generator $faker) {
    static $eventId;
    static $userId;

    return [
        'caption' => $faker->sentence(10),
        'description' => $faker->paragraph(),
        'number_of_comments' => 10,
        'number_of_likes' => 10,
    ];
});

$factory->define(App\Models\Quality::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Models\DonationType::class, function (Faker\Generator $faker) {
    static $qualityId;

    return [
        'name' => $faker->word,
        'quality_id' => $faker->randomElement($qualityId ?: $qualityId = App\Models\Quality::pluck('id')->toArray()),
    ];
});

$factory->define(App\Models\Donation::class, function (Faker\Generator $faker) {
    static $userId;
    static $eventId;
    static $campaignId;
    static $goalId;

    return [
        'user_id' => $faker->randomElement($userId ?: $userId = App\Models\User::pluck('id')->toArray()),
        'event_id' => $faker->randomElement($eventId ?: $eventId = App\Models\Event::pluck('id')->toArray()),
        'campaign_id' => $faker->randomElement($campaignId ?: $campaignId = App\Models\Campaign::pluck('id')->toArray()),
        'goal_id' => $faker->randomElement($goalId ?: $goalId = App\Models\Goal::pluck('id')->toArray()),
        'value' => rand(10, 1000),
        'status' => $faker->randomElement([0, 1]),
    ];
});

$factory->define(App\Models\Comment::class, function (Faker\Generator $faker) {
    static $userId;

    return [
        'user_id' => $faker->randomElement($userId ?: $userId = App\Models\User::pluck('id')->toArray()),
        'content' => $faker->paragraph(),
        'parent_id' => rand(1, 100),
        'number_of_comments' => 0,
        'number_of_likes' => 0,
    ];
});

$factory->define(App\Models\Like::class, function (Faker\Generator $faker) {
    static $userId;

    return [
        'user_id' => $faker->randomElement($userId ?: $userId = App\Models\User::pluck('id')->toArray()),
    ];
});

$factory->define(App\Models\Setting::class, function (Faker\Generator $faker) {
    return [
        'key' => rand(1, 100),
        'value' => rand(10, 1000),
    ];
});

$factory->define(App\Models\Media::class, function (Faker\Generator $faker) {
    return [
        'url_file' => $faker->imageUrl(),
        'type' => rand(0, 1),
    ];
});

$factory->define(App\Models\Expense::class, function (Faker\Generator $faker) {
    static $userId;
    static $eventId;
    static $goalId;

    return [
        'user_id' => $faker->randomElement($userId ?: $userId = App\Models\User::pluck('id')->toArray()),
        'event_id' => $faker->randomElement($eventId ?: $eventId = App\Models\Event::pluck('id')->toArray()),
        'goal_id' => $faker->randomElement($goalId ?: $goalId = App\Models\Goal::pluck('id')->toArray()),
        'time' => $faker->dateTime(),
        'cost' => rand(10, 100),
        'type' => rand(0, 1),
        'reason' => $faker->paragraph(),
    ];
});
