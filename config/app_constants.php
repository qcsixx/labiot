<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | TTL (Time To Live) values for various cache keys in seconds
    |
    */
    'cache' => [
        'ttl' => [
            'admin_dashboard_stats' => 300,  // 5 minutes
            'items_available_count' => 600,  // 10 minutes
            'items_available_list' => 600,   // 10 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Default pagination values for different views
    |
    */
    'pagination' => [
        'default' => 10,
        'dashboard_items' => 4,
        'notifications' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Upload paths and size limits
    |
    */
    'upload' => [
        'paths' => [
            'items' => 'items/',
            'users' => 'users/',
        ],
        'max_size' => 2048, // KB (2MB)
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif'],
    ],

    /*
    |--------------------------------------------------------------------------
    | DateTime Format Configuration
    |--------------------------------------------------------------------------
    |
    | Standard datetime formats used across the application
    |
    */
    'datetime' => [
        'format' => 'Y-m-d H:i:s',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i:s',
        'display_format' => 'd/m/Y H:i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for notification system
    |
    */
    'notification' => [
        'types' => [
            'borrow_request' => 'borrow_request',
            'borrow_approved' => 'borrow_approved',
            'borrow_rejected' => 'borrow_rejected',
            'return_reminder' => 'return_reminder',
            'overdue' => 'overdue',
        ],
    ],
];
