<?php

return [
    'checkin' => [
        'error' => 'Error',
        'errors' => [
            'no_data_found' => 'No information found',
            'event_not_found' => "Event not found :code",
            'client_not_found' => 'Information not found :qrcode',
            'duplicate_by_date' => 'Already checked in on the same day',
            'duplicate_by_user' => 'Already checked in at the same gate',
            'duplicate_checkin' => 'Already checked in',
        ],
        'success' => 'Check-in successful',
        'successes' => [
            'checkin_no_data' => 'Checked in with no input',
            'checkin_count' => 'Checked in: :count',
        ]
    ],
    'create' => [
        'success' => 'Created successfully',
    ],
    'recaptcha' => [
        'not_validated' => "The google recaptcha is not validated!.",
    ]
];
