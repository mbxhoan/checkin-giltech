<?php

return [
    'code' => 'GILTECH-VN',
    'company_name' => 'Giltech Solutions',
    'address' => 'Địa chỉ : A4-E23 Trường Sơn- Phường 02 - Tân Bình',
    'phone' => '08.39919084',
    'fax' => '08.39919085',
    'admin_email' => 'admin@giltech.com.vn',
    'admin_name' => 'System Admin',
    'domain' => 'giltech.com.vn',
    'internal_domain' => 'giltech.com.vn',
    'e-sign' => public_path('assets/images/e-sign.png'),
    'banner' => public_path('assets/images/banner.png'),
    'placeholders' => [
        'qrcode' => "assets/images/placeholders/qrcode.png",
        'image' => "assets/images/placeholders/image.jpg",
    ],

    'contact_note' => 'Vui lòng liên hệ Giltech Solutions hoặc hotline: 0973382111 – 0903855990 để được tư vấn và hỗ trợ.',

    'info' => [
        'email_1'   => 'admin@giltech.com.vn',
        'name'      => 'Giltech Solutions',
        'phone'     => '(+84) 902 639233',
        'fax'       => '(+84) 902 639233'
    ],

    'page' => [
        'theme' => [
            'color' => "444444",
        ],
        'logo_1' => [
            'title'                 => 'Giltech Solutions',
            // DEFAULT
            'internal_path'         => 'assets/images/logo.png',
            'internal_path_white'   => 'assets/images/logo-white.png',
            'convert_to_white'      => true,
            'dir_path'              => public_path('assets/images/logo.png'),
            'external_path'         => 'https://static.topcv.vn/company_logos/DLL50lpUflAoHUOP0I6Guw6gIw9WNTuO_1659641509____e44a8d4ace7b4d67158055b5b5bb7899.jpg',
            'alt'                   => 'Giltech Solutions logo',
        ],
        'logo_2' => [
            'title'                 => 'Giltech Solutions',
            'path'                  => '',
            'external_path'         => 'https://static.topcv.vn/company_logos/DLL50lpUflAoHUOP0I6Guw6gIw9WNTuO_1659641509____e44a8d4ace7b4d67158055b5b5bb7899.jpg',
            'alt'                   => 'Giltech Solutions logo',
        ],
        'link' => [
            'title'                 => 'Giltech Solutions',
            'href'                  => 'https://giltech.com.vn/',
            'text'                  => 'Giltech Solutions'
        ],
    ],
    'document' => [
        'internal_path'         => "downloads/[HDSD] Giltech Checkin v1.0.pdf",
        // 'internal_path'         => 'downloads/documents/DOCUMENT - Giltech CHECK-IN APP.pdf',
        'dir_path'              => public_path("downloads/[HDSD] Giltech Checkin v1.0.pdf"),
        'update_at'             => '25/06/2024'
    ],
    'credits' => [
        'rights'                => '© '.now()->format('Y').' Giltech Solutions. All rights reserved.',
        'name'                  => 'Giltech Solutions',
        'address'               => '38 Duong Phan Dinh Giot, Phuong 2, Tan Binh, Ho Chi Minh',
    ],
    'events'            => [
        'features'      => [
            "e-1"       => [
                'name'  => "Cập nhật thông tin sự kiện",
            ],
            "e-2"       => [
                'name'  => "Cấu hình trường thông tin",
            ],
            "e-3"       => [
                'name'  => "Cài đặt sự kiện",
            ],
            "e-4"       => [
                'name'  => "Tạo mới/Nạp danh sách khách mời",
            ],
            "e-5"       => [
                'name'  => "Nạp hình ảnh/background",
            ],
            "e-6"       => [
                'name'  => "Cấu hình Checkin",
            ],
            "e-7"       => [
                'name'  => "Landing page",
            ],
            // "e-8"       => [
            //     'name'  => "",
            // ],
            "e-9"       => [
                'name'  => "Tạo thiệp/thư mời",
            ],
            "e-10"      => [
                'name'  => "Tạo mẫu in Online",
            ],
        ]
    ],

    'packages'                  => [
        'basic'                 => [
            'name'              => 'Gói Basic',
            'full_name'         => 'Gói Tiết kiệm<br>(Basic)',
            'price'             => '5,000,000đ',
            'prev_price'        => '6,000,000đ',
            'discount'          => '16%',
            'link'              => '',
            'note'              => null,
            'expire_in'         => 30, // days
            'limited_clients'   => 200,
            'limited_events'    => 5,
            'limited_users'     => 10,
            'limited_emails'    => null,
            'enable'            => true,
            'events'            => [
                'features'      => [
                    "e-1",
                    "e-2",
                    "e-3",
                    "e-4",
                    "e-5",
                    "e-6",
                ]
            ],
            'excepts'           => [
                'menus'         => [
                    'landing_pages',
                    'campaigns',
                    'cards',
                    'labels',
                    'lucky_draws',
                ],
                'features'      => [
                    'landing_pages',
                    'emails',
                    'cards',
                    'labels',
                ],
                'routes'        => [
                    'admin.companys.*',
                    'admin.landing_pages.*',
                    'admin.landing_page_campaigns.*',
                    'admin.language_defines.*',
                    'admin.campaigns.*',
                    'admin.campaign_details.*',
                    'admin.emails.*',
                    'admin.email_templates.*',
                    'admin.email_senders.*',
                    'admin.cards.*',
                    'admin.card_details.*',
                    'admin.labels.*',
                    'admin.label_details.*',
                    'admin.media.*',
                ],
                'settings'      => [
                    'OPEN_LANDING_PAGE',
                    'ENABLE_FORM',
                    'ENABLE_CAPTCHA',
                    'REGISTER_CHECKIN',
                    'REGISTER_SEND_EMAIL',
                    'ALLOW_CHECKIN_PRINT',
                ],
            ],
            'showing_features'  => [
                'includes'      => [
                    1,2,3,4,5,8,11,12,13,
                    18,19,26,
                    27
                ],
                'specials'      => [
                    14          => "<200",
                    15          => "Không có",
                    16          => "Hỗ trợ",
                ]
            ]
        ],
        'pro'                   => [
            'name'              => 'Gói Pro',
            'full_name'         => 'Gói Chuyên nghiệp<br>(Profesional) <i class="fa-brands fa-product-hunt text-warning"></i>',
            'price'             => '12,000,000đ',
            'prev_price'        => '18,000,000đ',
            'discount'          => '12%',
            'link'              => '',
            'note'              => null,
            'expire_in'         => null,
            'limited_clients'   => 1000,
            'limited_events'    => null,
            'limited_users'     => null,
            'limited_emails'    => 1000,
            'enable'            => true,
            'showing_features'  => [
                'includes'      => [
                    1,2,3,4,5,6,7,8,9,10,11,12,13,
                    18,19,20,21,23,
                    27,28
                ],
                'specials'      => [
                    14          => "<1000",
                    15          => "<1000",
                    16          => "Tùy chọn",
                ]
            ]
        ],
        'vip'                   => [
            'name'              => 'Gói Vip',
            'full_name'         => 'Gói Cao cấp<br>(VIP) <i class="fa-solid fa-crown text-warning"></i>',
            'price'             => 'Liên hệ',
            'prev_price'        => '30,000,000đ',
            'discount'          => '',
            'link'              => '',
            'note'              => 'Liên hệ',
            'expire_in'         => null,
            'limited_clients'   => null,
            'limited_events'    => null,
            'limited_users'     => null,
            'limited_emails'    => null,
            'enable'            => false,
            'showing_features'  => [
                'includes'      => [],
                'specials'      => []
            ]
        ],
    ],
    'packages_features'         => [
        'A'                     => [
            'name'              => 'Tính năng các phiên bản',
            'details'           => [
                1               => 'Xử lý dữ liệu vé mới import',
                2               => 'Tạo mã Qrcode vé mời',
                3               => 'Xuất mã Qrcode vé mời',
                4               => 'Thay đổi background Web check-in',
                5               => 'Thay đổi background PDA check-in',
                6               => 'Gửi mail vé mời tự động',
                7               => 'Đăng ký vé mời qua Landing page',
                8               => 'Quét mã Qrcode check-in',
                9               => 'Quét mã Qrcode check-out',
                10              => 'Quay số Lucky draw',
                11              => 'Báo cáo realtime sự kiện',
                12              => 'Quản lý phân quyền',
                13              => 'Quản lý sự kiện',
                14              => 'Số lượng vé mời',
                15              => 'Số lượng mail',
                16              => 'Cấu hình theo tên miền riêng',
                // 17               => 'Giá bán',
            ]
        ],
        'B'                     => [
            'name'              => 'Các gói tùy chọn',
            'details'           => [
                18              => 'Hướng dẫn sử dụng trực tiếp/online',
                19              => 'Nhân sự hỗ trợ kỹ thuật tại sự kiện',
                20              => 'In tem/thiệp mã Qecode check-in',
                21              => 'Thêm Qrcode vào thiệp',
                22              => 'Landing page đa ngôn ngữ',
                23              => 'Check-in tại Booth triển lãm',
                24              => 'Tích  hợp Google sheet/Web/CRM..',
                25              => 'Tích hợp thanh toán Payoo, ví điện tử',
                26              => 'Quét mã Qrcode check-in bằng camera'
            ]
        ],
        'C'                     => [
            'name'              => 'Thiết bị',
            'details'           => [
                27              => 'Nhóm Khách hội nghị, sự kiện',
                28              => 'Nhóm Khách hội chợ triển lãm',
                29              => 'Nhóm Khách lễ hội , âm nhạc',
            ]
        ],
    ],
    'devices'                   => [
        'pdas'                  => 'Máy PDA chuyên dụng',
        'scanners'              => 'Bộ máy scan Qrcode+ Laptop',
        'printers'              => 'Máy in thiệp/ decal Qrcode',
        'standees'              => 'Standee',
    ],
    'tutors'                    => [
        'event-detail-2'        => [
            'title'             => 'Thông tin về các trường thông tin',
            'content'           => 'Các thông tin sẽ quy định đầu vào data sự kiện của bạn, đồng thời khi nhập liệu thêm mới cũng có bao gồm những thông tin đó.',
        ],
        'event-detail-3'        => [
            'title'             => 'Thông tin về cài đặt sự kiện',
            'content'           => 'Các thiết lập tại đây sẽ hiển thị cũng như là quyết định về tuỳ chỉnh mặt tính năng cho các phần như Qrcode, Landing page và màn hình checkin của bạn.',
        ],
        'event-detail-4'        => [
            'title'             => 'Thông tin về Tạo mời/Nạp danh sách khách mời',
            'content'           => 'Bạn có thể tạo sẵn một lượng data nhất định (có thể gom nhóm bằng cách nhập vào <b>Tên nhóm</b>) hoặc bạn có thể lấy template nạp file để nạp danh sách tuỳ ý.',
        ],
        'event-detail-5'        => [
            'title'             => 'Thông tin về Nạp ảnh/background',
            'content'           => 'Nơi đây lưu trữ các file ảnh như logo, favicon, background và có thể lưu ảnh của bạn online phục vụ cho mục đích hiển thị trên màn hình.',
        ],
        // 'event-detail-6'        => [
        //     'title'             => 'Thông tin về Landing page',
        //     'content'           => 'Nơi đây lưu trữ các file ảnh như logo, favicon, background và có thể lưu ảnh của bạn online phục vụ cho mục đích hiển thị trên màn hình.',
        // ],
        // 'event-detail-7'        => [
        //     'title'             => 'Thông tin về Nạp ảnh/background',
        //     'content'           => 'Nơi đây lưu trữ các file ảnh như logo, favicon, background và có thể lưu ảnh của bạn online phục vụ cho mục đích hiển thị trên màn hình.',
        // ],
    ],
    'quotes' => [
        "Đừng bào chữa cho lỗi lầm, hãy cải tiến.",
        "Đừng dừng lại khi mệt mỏi, chỉ dừng lại khi đã xong.",
        "Đằng sau người thành công là rất nhiều năm không thành công.",
        "Sống theo cách mà nếu có ai đó nói xấu bạn, sẽ không ai tin điều đó.",
        "Trung thực là món quà tốn kém, không mong đợi từ người rẻ tiền.",
        "Hãy luôn là chính mình, đừng lúng túng khi không cùng suy nghĩ với người khác.",
        "Bạn đang khó khăn không có nghĩa là thất bại.",
        "Giới hạn duy nhất khi kinh doanh chính là ở suy nghĩ của bạn.",
        "Hãy giữ sự thành công cho riêng mình.",
        "Điều khó mở nhất là một tâm trí khép kín.",
        "Đồng vốn không khan hiếm, tầm nhìn mới khan hiếm",
        "Thay vì tìm kiếm cơ hội, hãy tạo ra chúng.",
    ],
    'dashboard' => [
        'steps' => [
            [
                'text'  => 'Tạo sự kiện <i class="fa-regular fa-plus-square text-primary"></i>',
                'route' => 'admin.events.create',
            ],
            [
                'text'  => 'Thêm khách mời <i class="fa-regular fa-plus-square text-primary"></i>',
                'route' => 'admin.events.index',
            ],
            [
                'text'  => 'Nạp background <i class="fa-solid fa-images text-primary"></i>',
                'route' => 'admin.events.index',
            ],
            [
                'text'  => 'Checkin <i class="fa-solid fa-barcode text-primary"></i>',
                'route' => 'admin.events.index',
            ],
            [
                'text'  => 'Báo cáo <i class="fa-solid fa-file-export text-primary"></i>',
                'route' => 'admin.reports.index',
            ],
        ]
    ],
    'files' => [
        'extensions'        => [
            'image'         => [
                'text'      => 'Hình ảnh',
                'accepts'   => [
                    // 'image/*',              // All image types
                    'image/jpg'     => '.jpg',
                    'image/jpeg'    => '.jpeg',
                    'image/png'     => '.png',
                    'image/gif'     => '.gif',
                    'image/webp'    => '.webp',
                    'image/svg+xml' => '.svg',
                    'image/heic'    => '.heic',
                ]
            ],
            'video'         => [
                'text'      => 'Audio',
                'accepts'   => [
                    // 'video/*'        => '',              // All video types
                    'video/mp4'         => '.mp4',
                    'video/webm'        => '.webm',
                    'video/ogg'         => '.ogv,.ogg',
                    'video/mpeg'        => '.mpeg,.mpg',
                    'video/quicktime'   => '.mov',
                ]
            ],
            'audio'         => [
                'text'      => 'Video',
                'accepts'   => [
                    // 'audio/*',              // All audio types
                    'audio/mpeg'    => '.mpeg,.mp3',
                    'audio/mp3'     => '.mp3',
                    'audio/ogg'     => '.ogg',
                    'audio/wav'     => '.wav',
                ]
            ],
            'document'      => [
                'text'      => 'Tài liệu',
                'accepts'   => [
                    'application/pdf' => '.pdf',
                    'application/msword' => '.doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
                    'application/vnd.ms-excel' => '.xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
                    'application/vnd.ms-powerpoint' => '.ppt',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
                    'text/plain' => '.txt',
                ]
            ],
        ]
    ]
];
