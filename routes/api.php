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
Route::middleware('auth:api')->get('/user', 'Api\UserController@authUser');
Route::get('/show-campaign', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api', 'middleware' => ['xssProtection']], function () {
    Route::group(['namespace' => 'Auth'], function () {
        Route::post('login', 'AuthController@login')->name('login');
        Route::post('register', 'RegisterController@register')->name('register');
        Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
        Route::post('password/reset', 'ResetPasswordController@reset');

    });
    Route::get('homepage', 'CampaignController@getHomepage')->name('homepage');
    Route::get('get-slug/{type}/{id}', 'CommonController@getPageSlug');
    Route::post('check-exist', 'CommonController@checkExist');

    Route::group(['prefix' => 'user/{id}', 'as' => 'user.'], function () {
        Route::get('/', 'UserController@getTimeLine')->name('getTimeLine');
        Route::get('friends/{page}', 'UserController@listFriends')->name('friends');
        Route::get('get-photos-user', 'UserController@getPhotos')->name('get-photos-friend');
        Route::get('get-photos-friends', 'UserController@getPhotosAndFriends')->name('get-photos-user');
        Route::get('search-friends/{keyword}', 'UserController@searchFriends')->name('search_friend');
        Route::get('owned-campaign', 'UserController@listOwnedCampaign')->name('owned-campaign');
        Route::get('joined-campaign', 'UserController@listJoinedCampaign')->name('joined-campaign');
        Route::get('following-campaign', 'UserController@listFollowingCampaign')->name('following-campaign');
        Route::get('following-tag', 'UserController@listFollowingTag')->name('following-tag');
        Route::get('get-user', 'UserController@getUser')->name('user');
        Route::get('list-close-campaign', 'UserController@listClosedCampaign');
    });

    Route::resource('tag', 'TagController', ['only' => ['index', 'show']]);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'Auth\AuthController@logout')->name('logout');
        Route::post('send-message', 'ChatController@sendMessage');
        Route::post('send-message-to-group', 'ChatController@sendMessageToGroup');
        Route::get('show-message/{id}', 'ChatController@showMessages');
        Route::get('show-notifications', 'ChatController@getNotification');
        Route::get('list-notification', 'UserController@listNotification');
        Route::get('total-unread-notifications', 'UserController@totalUnreadNotifications');
        Route::post('like/{modelId}/{flag}', 'LikeController@like')->name('like');
        Route::delete('delete-photo/{mediaId}', 'UserController@deletePhoto')->name('delete-photo');
        Route::get('search/{page}/{quantity}/{type}', 'CampaignController@search')->name('search');
        Route::get('list-members/{modelId}/{flag}', 'LikeController@getListMemberLiked');

        Route::group(['as' => 'user.'], function () {
            Route::group(['prefix' => 'settings'], function () {
                Route::patch('password', 'UserController@updatePassword')->name('password');
                Route::patch('profile', 'UserController@updateProfile')->name('profile');
                Route::post('avatar', 'UserController@updateAvatar')->name('avatar');
                Route::post('header-photo', 'UserController@updateHeaderPhoto')->name('header');
                Route::post('upload-files/{path}', 'UserController@uploadImages')->name('upload');
            });

            Route::get('news-feed', 'ActivityController@getNewsFeed')->name('newsfeed');
            Route::get('friends-suggest', 'UserController@friendsSuggest')->name('friends-suggest');
            Route::patch('follow-tag/{id}', 'UserController@followTag')->name('follow-tag');
            Route::patch('join-campaign/{id}', 'UserController@joinCampaign')->name('join-campaign');
            Route::get('list-user-following', 'UserController@getListFollow')->name('list-user-following');
            Route::post('unfriend/{id}', 'UserController@unfriend')->name('unfriend');
            Route::post('send-friend-request/{id}', 'UserController@sendFriendRequestTo')->name('send-friend-request');
            Route::post('accept-friend-requset/{id}', 'UserController@acceptFriendRequestFrom')->name('accept-friend-request');
            Route::post('deny-friend-request/{id}', 'UserController@denyFriendRequestFrom')->name('deny-friend-request');
            Route::get('list-request-friend', 'UserController@getNotificationRequest');
            Route::post('reject-request', 'UserController@rejectRequest');
            Route::post('mark-all-read', 'UserController@markRead');
            Route::post('mark-read-notifications', 'UserController@markReadNotifications');
        });

        Route::group(['prefix' => '/campaign', 'as' => 'campaign.'], function () {
            Route::patch('change-role', 'CampaignController@changeMemberRole')->name('change-role');
            Route::patch('remove-user', 'CampaignController@removeUser')->name('remove-user');
            Route::patch('change-owner', 'CampaignController@changeOwner')->name('change-owner');
            Route::post('change-status-user/{campaignId}/{userId}/{flag}', 'CampaignController@changeStatusMember')->name('approve');
            Route::get('get/tags', 'CampaignController@getTags')->name('tags');
            Route::get('involve', 'CampaignController@getCampaignInvolve')->name('campaign-involve');
            Route::get('/{id}/timeline/event', 'CampaignController@getListEvent');
            Route::get('members/{campaignId}/{status}', 'CampaignController@members')->name('members');
            Route::post('attend-campaign/{id}/{flag}', 'CampaignController@attendCampaign')->name('attendCampaign');
            Route::get('list-photos/{id}', 'CampaignController@listPhotos');
            Route::get('campaign-related/{id}', 'CampaignController@getCampaignRelated');
            Route::get('search-members/{campaignId}/{status}', 'CampaignController@searchMembers');
            Route::get('{id}/statistic', 'CampaignController@statistic')->name('statistic');
            Route::get('{id}/user-statistic', 'CampaignController@statisticWithUser')->name('userStatistic');
            Route::get('{id}/get-export-data', 'CampaignController@getExportData')->name('export');
            Route::get('events-closed/{campaignId}', 'CampaignController@getEventsClosed');
            Route::post('open/{id}', 'CampaignController@openCampaign');
            Route::get('search-members-invite/{campaignId}', 'CampaignController@searchMemberToInvites');
            Route::post('invite-user/{campaignId}/{userId}', 'CampaignController@inviteUser');
            Route::post('accept/{campaignId}', 'CampaignController@acceptInvitation');
        });

        Route::resource('campaign', 'CampaignController', ['only' => ['store', 'update', 'destroy', 'show', 'edit']]);

        Route::group(['prefix' => '/event', 'as' => 'event.'], function () {
            Route::post('create', 'EventController@create')->name('create');
            Route::get('/update/{id}', 'EventController@edit')->name('update-event');
            Route::patch('/update/{id}', 'EventController@update')->name('update-event');
            Route::patch('/update-setting/{id}', 'EventController@updateSetting')->name('update-setting');
            Route::post('like/{eventId}', 'EventController@like')->name('like');
            Route::get('show/{id}', 'EventController@show');
            Route::get('donation', 'EventController@getTypeQuality')->name('getTypeQuality');
            Route::get('check-permission/{id}', 'EventController@checkIfUserCanManageEvent')->name('check-permission');
            Route::delete('delete/{id}', 'EventController@destroy');
            Route::get('/get-info-event/{id}', 'EventController@getInfoEvent')->name('getInfoEvent');
            Route::patch('open/{id}', 'EventController@openEvent');
        });

        Route::group(['prefix' => '/action', 'as' => 'action.'], function () {
            Route::patch('update/{id}', 'ActionController@update')->name('update');
            Route::delete('delete/{id}', 'ActionController@delete')->name('delete');
            Route::post('like/{actionId}', 'ActionController@like')->name('like');
            Route::get('list/{eventId}', 'ActionController@listAction');
            Route::get('search/{eventId}', 'ActionController@searchAction');
            Route::post('create', 'ActionController@store')->name('create');
            Route::get('{id}', 'ActionController@show');
        });

        Route::resource('/comment', 'CommentController', ['only' => ['destroy']]);

        Route::group(['prefix' => '/comment', 'as' => 'comment.'], function () {
            Route::post('/create-comment/{modelId}/{parentId}/{flag}', 'CommentController@createComment')->name('create');
            Route::post('/update-comment/{id}/{flag}', 'CommentController@updateComment')->name('update');
            Route::get('/sub-comment/{parentId}', 'CommentController@getSubComment');
            Route::get('/{modelId}/{flag}', 'CommentController@show');
        });

        Route::group(['prefix' => '/donation'], function () {
            Route::patch('/update-status/{id}', 'DonationController@updateStatus')->name('update-status');
            Route::post('create-many', 'DonationController@createMany');
            Route::resource('donation', 'DonationController', ['only' => ['store', 'update', 'destroy']]);
        });

        Route::post('expense-create-buy', 'ExpenseController@createBy')->name('expense-create-buy');
        Route::patch('expense-buy/{id}', 'ExpenseController@updateExpenseBuy')->name('expense-update-buy');
        Route::get('event/{id}/statistic', 'ExpenseController@statistic')->name('expense-statistic');
        Route::get('get-list-expense', 'ExpenseController@getList')->name('get-list-expense');
        Route::get('get-statistic-data', 'ExpenseController@getStatisticData')->name('get-statistic-data');
        Route::resource('expense', 'ExpenseController', ['only' => ['index', 'store', 'update', 'destroy']]);
        Route::resource('product', 'ProductController', ['only' => ['index']]);
        Route::resource('goal', 'GoalController', ['only' => ['index', 'destroy']]);

        Route::group(['prefix' => 'file', 'as' => 'file'], function () {
            Route::post('upload', 'UploadController@upload')->name('upload');
            Route::post('upload-image-for-editor', 'CommonController@uploadImageForEditor');
            Route::get('quill-uploaded-images', 'CommonController@quillUploadedImages');
            Route::delete('delete/{path?}', 'UploadController@delete')->name('delete')->where('path', '.+');
        });
    });
});
