<?php
    $menus = [
        'admin' => [
            'dashboard' => [
                'route'         => 'admin.dashboard',
                'route_prefix'  => 'admin.dashboard',
                'x_icon_name'   => 'tachometer',
                'x_icon_prefix' => null,
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
            ],
            'companys' => [
                'route'         => 'admin.companys.index',
                'route_prefix'  => 'admin.companys.*',
                'x_icon_name'   => 'building',
                'x_icon_prefix' => null,
                'text'          => 'Công ty',
                'is_admin'      => true,
            ],
            'events' => [
                'route'         => 'admin.events.index',
                'route_prefix'  => [
                    'admin.events.*',
                    'admin.clients.*',
                    'admin.checkins.*',
                    'admin.landing_pages.create',
                    'admin.landing_pages.edit',
                    'admin.landing_pages.select-event-to-create',
                    'admin.custom_field_templates.*',
                    'admin.cards.create',
                    'admin.cards.edit',
                    'admin.cards.create',
                    'admin.labels.*',
                    'admin.label_details.*',
                    'admin.events.tickets.*',
                ],
                'x_icon_name'   => 'calendar-days',
                'x_icon_prefix' => null,
                'text'          => 'Sự kiện',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
            ],
            'tickets' => [
                'route'         => 'admin.tickets.index',
                'route_prefix'  => [
                    'admin.tickets.*',
                    'admin.events.tickets.*',
                ],
                'x_icon_name'   => 'ticket',
                'x_icon_prefix' => 'fa-solid',
                'text'          => 'Vé',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                ],
            ],
            'reports' => [
                'route'         => 'admin.reports.index',
                'route_prefix'  => [
                    'admin.reports.*',
                ],
                'x_icon_name'   => 'outdent',
                'x_icon_prefix' => null,
                'text'          => 'Báo cáo',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
            ],
            'features' => [
                'x_icon_name'   => 'box-open',
                'x_icon_prefix' => 'fa-solid',
                'route_prefix'  => [
                    'admin.campaigns.*',
                    'admin.landing_pages.*',
                ],
                'is_admin'      => true,
                'text'          => 'Tính năng',
                'roles'         => [
                    'admin',
                ],
                'subMenus'      => [
                    'campaigns' => [
                        'route'         => 'admin.campaigns.index',
                        'route_prefix'  => [
                            'admin.campaigns.*',
                            'admin.campaign_details.*',
                            'admin.emails.*',
                            'admin.email_templates.*',
                        ],
                        'x_icon_name'   => 'envelope',
                        'x_icon_prefix' => null,
                        'text'          => 'Email',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'landing_pages' => [
                        'route'         => 'admin.landing_pages.index',
                        'route_prefix'  => 'admin.landing_pages.*',
                        'text'          => 'Landing pages',
                        'x_icon_name'   => 'file',
                        'x_icon_prefix' => 'fa-solid',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'cards' => [
                        'route'         => 'admin.cards.index',
                        'route_prefix'  => [
                            'admin.cards.*',
                            'admin.card_details.*',
                        ],
                        'x_icon_name'   => 'images',
                        'x_icon_prefix' => null,
                        'text'          => 'Thiệp/Thư mời',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'labels' => [
                        'route'         => 'admin.labels.index',
                        'route_prefix'  => [
                            'admin.labels.*',
                            'admin.label_details.*',
                        ],
                        'x_icon_name'   => 'print',
                        'x_icon_prefix' => null,
                        'text'          => 'Mẫu in',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'lucky_draws' => [
                        'route'         => 'admin.lucky_draws.index',
                        'route_prefix'  => [
                            'admin.lucky_draws.*',
                            'admin.lucky_draw_clients.*',
                            'admin.lucky_draw_rewards.*',
                        ],
                        'x_icon_name'   => 'dice',
                        'x_icon_prefix' => null,
                        'text'          => 'Quay số',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ],
            ],
            'management' => [
                'x_icon_name'   => 'box-open',
                'x_icon_prefix' => 'fa-solid',
                'route_prefix'  => [
                    'admin.users.*',
                    'admin.email_senders.*',
                ],
                'is_admin'      => true,
                'text'          => 'Quản lý',
                'roles'         => [
                    'admin',
                ],
                'subMenus'      => [
                    'users' => [
                        'route'         => 'admin.users.index',
                        'route_prefix'  => 'admin.users.*',
                        'x_icon_name'   => 'user',
                        'x_icon_prefix' => null,
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'senders' => [
                        'route'         => 'admin.email_senders.index',
                        'route_prefix'  => 'admin.email_senders.*',
                        'x_icon_name'   => 'users',
                        'x_icon_prefix' => null,
                        'is_admin'      => true,
                        'text'          => 'Senders',
                        'roles'         => [],
                    ],
                ]
            ],
            'media' => [
                'route'         => 'admin.media.index',
                'route_prefix'  => 'admin.media.*',
                'x_icon_name'   => 'file',
                'x_icon_prefix' => 'fa-regular',
                'is_admin'      => true,
            ],
        ],
    ];

return $menus;
