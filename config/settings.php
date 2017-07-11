<?php

return [
    'admin_system' => 'admin system',
    'comment_parent' => 0,
    'paginate_default' => 15,
    'locale' => ['vi', 'en', 'ja'],
    'campaigns' => [
        'status' => 1, // set campaign public or private
        'limit' => 2, // limit user join campaign
        'timeout_campaign' => 3, // set timeout for campaign
        'start_day' => 9,
        'end_day' => 10,
    ],
    'events' => [
        'start_day' => 7,
        'end_day' => 8,
        'timeout_event' => 4, // set timeout for event
        'set_image' => 5, // set image for event
        'set_video' => 6, // set video for event
    ],
    'actions' => [
        //
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
    ]
];
