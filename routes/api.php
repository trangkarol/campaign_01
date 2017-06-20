<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/show-campaign', function (Request $request) {
    return $request->user();
});
Route::group(['namespace' => 'Api', 'middleware' => ['xssProtection']], function () {
    Route::group(['namespace' => 'Auth'], function () {
        Route::post('login', 'AuthController@login')->name('login');
        Route::post('register', 'RegisterController@register')->name('register');
    });

    Route::group(['as' => 'user.', 'prefix' => 'settings'], function () {
        Route::patch('password', 'UserController@updatePassword')->name('password');
        Route::patch('profile', 'UserController@updateProfile')->name('profile');
        Route::post('avatar', 'UserController@updateAvatar')->name('avatar');
        Route::post('header-photo', 'UserController@updateHeaderPhoto')->name('header');
        Route::patch('follow/{id}', 'UserController@follow')->name('follow');
    });

    Route::group(['prefix' => 'user/{id}', 'as' => 'user.'], function () {
        Route::get('followers', 'UserController@listFollower')->name('follower');
        Route::get('followings', 'UserController@listFollowing')->name('following');
        Route::get('owned-campaign', 'UserController@listOwnedCampaign')->name('owned-campaign');
        Route::get('joined-campaign', 'UserController@listJoinedCampaign')->name('joined-campaign');
    });

    Route::post('logout', 'Auth\AuthController@logout')->name('logout');

    Route::post('check-exist', 'CommonController@checkExist');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'Auth\AuthController@logout')->name('logout');

        Route::group(['prefix' => '/campaign', 'as' => 'campaign.'], function () {
            Route::post('like/{campaignId}', 'CampaignController@like')->name('like');
            Route::patch('change-role', 'CampaignController@changeMemberRole')->name('change-role');
            Route::patch('remove-user', 'CampaignController@removeUser')->name('remove-user');
            Route::patch('change-owner', 'CampaignController@changeOwner')->name('change-owner');
            Route::post('approve-user', 'CampaignController@approveUserJoinCampaign')->name('approve');
            Route::get('/{id}/timeline/event', 'CampaignController@getListEvent');
        });

        Route::resource('campaign', 'CampaignController', ['only' => ['store', 'update', 'destroy', 'show']]);

        Route::group(['prefix' => '/event', 'as' => 'event.'], function () {
            Route::post('create', 'EventController@create')->name('create');
            Route::patch('/update/{id}', 'EventController@update')->name('update-event');
            Route::patch('/update-setting/{id}', 'EventController@updateSetting')->name('update-setting');
            Route::post('like/{eventId}', 'EventController@like')->name('like');
            Route::group(['prefix' => '/comment', 'as' => 'comment.'], function () {
                Route::post('create', 'CommentController@createCommentEvent')->name('create');
            });
        });

        Route::group(['prefix' => '/action', 'as' => 'action.'], function () {
            Route::patch('/update/{id}', 'ActionController@update')->name('update');
            Route::post('like/{actionId}', 'ActionController@like')->name('like');
            Route::group(['prefix' => '/comment', 'as' => 'comment.'], function () {
                Route::post('create', 'CommentController@createCommentAction')->name('create');
            });
        });

        Route::resource('/comment', 'CommentController', ['only' => ['update', 'destroy', 'show']]);

        Route::group(['prefix' => '/comment', 'as' => 'comment.'], function () {
            Route::post('/create-comment-event/{modelId}', 'CommentController@createCommentEvent');
        });

        Route::group(['prefix' => '/donation'], function () {
            Route::patch('/update-status/{id}', 'DonationController@updateStatus')->name('update-status');
            Route::resource('donation', 'DonationController', ['only' => ['store', 'update', 'destroy']]);
        });
    });
});
