<?php
    return [
        /*
        |--------------------------------------------------------------------------
        | Menuable
        |--------------------------------------------------------------------------
        |
        | set the endpoint to get menus
        |
        */
        'path'  => 'nila/menus/{key?}',

        /*
        |--------------------------------------------------------------------------
        | Menus
        |--------------------------------------------------------------------------
        |
        | create a genesis menu to start
        |
        */
        'menus' => [
            [
                'key' => 'user-profile',

                'order'       => 2,
                'permissions' => [ 'user-view' ],

                'title' => 'users',
                'icon'  => 'users-icon',
                'class' => 'users-info',
                'link'  => 'v1.users.index',

                'children' => [
                    [
                        'key'         => 'user-profile-update',
                        'order'       => 2,
                        'permissions' => [ 'user-update' ],

                        'title' => 'update user',
                        'icon'  => 'user-icon',
                        'link'  => 'v1.users.index',

                        'children' => [
                            [
                                'key'         => 'user-profile-update-export',
                                'order'       => 2,
                                'permissions' => [ 'user-update' ],

                                'title' => 'export user',
                                'icon'  => 'user-icon',
                                'link'  => 'v1.users.index'
                            ]

                        ]
                    ],
                    [
                        'key'         => 'user-profile-show',
                        'order'       => 2,
                        'permissions' => [ 'user-update' ],

                        'title' => 'show user',
                        'icon'  => 'user-icon',
                        'link'  => 'v1.users.index'
                    ]
                ]
            ],
            [
                'key'         => 'user-dashboard',
                'order'       => 2,
                'permissions' => [ 'user-update' ],

                'title' => 'dashboard user',
                'icon'  => 'user-icon',
                'link'  => 'v1.users.index',

                'children' => [
                    [
                        'key'         => 'user-dashboard-edit',
                        'order'       => 2,
                        'permissions' => [ 'user-update' ],

                        'title' => 'dashboard user edit',
                        'icon'  => 'user-icon',
                        'link'  => 'v1.users.index'
                    ]
                ]
            ],
        ],
    ];
