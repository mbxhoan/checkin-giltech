<?php
    $urlImage = env('APP_URL', 'http://localhost');

    return [
        'frontend' => [
            'home' => [
                'title'         => env('APP_NAME'),
                'description'   => 'Delfi Checkin Sự kiện',
                'robots'        => 'index',
                'image'         => "{$urlImage}/meta/pages/home.png",
            ]
        ]
    ];
