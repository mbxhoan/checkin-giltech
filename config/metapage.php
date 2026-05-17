<?php
    $urlImage = env('APP_URL', 'http://localhost');

    return [
        'frontend' => [
            'home' => [
                'title'         => env('APP_NAME'),
                'description'   => 'Giltech Solutions event and check-in platform',
                'robots'        => 'index',
                'image'         => "{$urlImage}/meta/pages/home.png",
            ]
        ]
    ];
