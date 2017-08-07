<?php

return [
    'admin_system' => 'admin system',
    'comment_parent' => 0,
    'search_default' => 0,
    'paginate_default' => 15,
    'paginate_notification' => 5,
    'paginate_comment' => 2,
    'paginate_event' => 4,
    'flag_join' => 'join',
    'locale' => ['vi', 'en', 'ja'],
    'campaigns' => [
        'status' => 1, // set campaign public or private
        'limit' => 2, // limit user join campaign
        'timeout_campaign' => 3, // set timeout for campaign
        'start_day' => 9,
        'end_day' => 10,
        'not_approve' => 0,
        'approve' => 1,
    ],
    'events' => [
        'start_day' => 7,
        'end_day' => 8,
        'timeout_event' => 4, // set timeout for event
        'set_image' => 5, // set image for event
        'set_video' => 6, // set video for event
    ],
    'actions' => [
        'paginate_in_event' => 15,
    ],
    'value_of_settings' => [
        'status' => [ // key of setting
            'private' => 0,
            'public' => 1,
        ],
    ],
    'pagination' => [
        'follow' => 12,
        'timeline' => 5,
        'following_campaign' => 3,
        'like' => 20,
    ],
    'group_chat' => 'groupChat',
    'single_chat' => 'singleChat',
    'head_room_name' => 'hashtag:',
    'notifications' => 'notifications',
    'folderQuill' => 'quill',
];
