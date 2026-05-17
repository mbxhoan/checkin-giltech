<?php

use App\Models\EventSetting;

return [
    /****************************************** CONFIG ******************************************/

    'LANDING_PAGE' => [
        // 'open_landing_page'     => [
        //     'name'          => 'OPEN_LANDING_PAGE',
        //     'description'   => "Cho phép mở landing page",
        //     'value'         => 0,
        //     'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        // ],
        'enable_form' => [
            // 'parent'        => 'open_landing_page',
            'name'          => 'ENABLE_FORM',
            'description'   => "Cho phép mở form",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'enable_captcha' => [
            'parent'        => 'enable_form',
            'name'          => 'ENABLE_CAPTCHA',
            'description'   => "Mở xác thực captcha trong đăng ký",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'register_checkin' => [
            'parent'        => 'enable_form',
            'name'          => 'REGISTER_CHECKIN',
            'description'   => "Đăng ký thành công đánh dấu checkin",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'register_send_email' => [
            'parent'        => 'enable_form',
            'name'          => 'REGISTER_SEND_EMAIL',
            'description'   => "Đăng ký nhận email",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        // 'register_send_email' => [
        //     'parent'        => 'enable_form',
        //     'name'          => 'REGISTER_SEND_EMAIL',
        //     'description'   => "Đăng ký nhận email",
        //     'value'         => 0,
        //     'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        // ],
        'open_name_card_ocr' => [
            'parent'        => 'enable_form',
            'name'          => 'OPEN_NAME_CARD_OCR',
            'description'   => "Mở tính năng đọc thẻ tên",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'open_scan_cccd' => [
            'parent'        => 'enable_form',
            'name'          => 'OPEN_SCAN_CCCD',
            'description'   => "Mở tính năng quét CCCD",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
    ],

    'PAYMENT' => [
        'cash_payment_enabled' => [
            'name'          => 'CASH_PAYMENT_ENABLED',
            'description'   => 'Bật thanh toán tiền mặt tại quầy',
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH,
        ],
        'cash_payment_start_at' => [
            'name'          => 'CASH_PAYMENT_START_AT',
            'description'   => 'Thời điểm bắt đầu hiển thị thanh toán tiền mặt',
            'value'         => null,
            'input_type'    => EventSetting::INPUT_TYPE_TEXT,
        ],
        'cash_payment_end_at' => [
            'name'          => 'CASH_PAYMENT_END_AT',
            'description'   => 'Thời điểm kết thúc hiển thị thanh toán tiền mặt',
            'value'         => null,
            'input_type'    => EventSetting::INPUT_TYPE_TEXT,
        ],
        'cash_payment_label' => [
            'name'          => 'CASH_PAYMENT_LABEL',
            'description'   => 'Tên hiển thị cho phương thức tiền mặt',
            'value'         => 'Thanh toán tiền mặt tại quầy',
            'input_type'    => EventSetting::INPUT_TYPE_TEXT,
        ],
        'cash_payment_instruction' => [
            'name'          => 'CASH_PAYMENT_INSTRUCTION',
            'description'   => 'Hướng dẫn hiển thị khi khách chọn thanh toán tiền mặt',
            'value'         => 'Vui lòng thanh toán trực tiếp tại quầy trong ngày sự kiện.',
            'input_type'    => EventSetting::INPUT_TYPE_TEXT,
        ],
        'cash_requires_staff_confirmation' => [
            'name'          => 'CASH_REQUIRES_STAFF_CONFIRMATION',
            'description'   => 'Nhân viên phải xác nhận thủ công trước khi kích hoạt vé',
            'value'         => 1,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH,
        ],
    ],

    'QRCODE' => [
        // 'generate_complex_qrcode' => [
        //     'name'          => 'GENERATE_COMPLEX_QRCODE',
        //     'description'   => "Tạo Qrcodes phức tạp",
        //     'value'         => 0,
        //     'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        // ],
        'qrcode_attach_logo' => [
            'name'          => 'QRCODE_ATTACH_LOGO',
            'description'   => "Ảnh Qrcode đính kèm logo",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'qrcode_logo_width' => [
            'parent'        => 'qrcode_attach_logo',
            'name'          => 'QRCODE_LOGO_WIDTH',
            'description'   => "Chiều rộng logo đính kèm",
            'value'         => 0.3,
            'options'       => json_encode(EventSetting::getOptionsQrcodeLogoWidth()),
            'input_type'    => EventSetting::INPUT_TYPE_SELECT
        ],
        'qrcode_attach_text' => [
            'name'          => 'QRCODE_ATTACH_TEXT',
            'description'   => "Ảnh Qrcode đính kèm chuỗi mã",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'generate_custom_qrcode' => [
            'name'          => 'GENERATE_CUSTOM_QRCODE',
            'description'   => "Tạo Qrcodes theo trường thông tin động",
            'value'         => null,
            'input_type'    => EventSetting::INPUT_TYPE_TEXT
        ],
        'custom_file_name' => [
            'name'          => 'CUSTOM_FILE_NAME',
            'description'   => "Tên file theo trường thông tin động",
            'value'         => null,
            'input_type'    => EventSetting::INPUT_TYPE_TEXT
        ],
        'qrcode_color' => [
            'name'          => 'QRCODE_COLOR',
            'description'   => "Màu Qrcode",
            'value'         => "#000000",
            'input_type'    => EventSetting::INPUT_TYPE_COLOR
        ],
        'qrcode_bg_color' => [
            'name'          => 'QRCODE_BG_COLOR',
            'description'   => "Màu nền Qrcode",
            'value'         => "#ffffff",
            'input_type'    => EventSetting::INPUT_TYPE_COLOR
        ],
        'qrcode_correction' => [
            'name'          => 'QRCODE_CORRECTION',
            'description'   => 'Độ chi tiết Qrcode <br> <span class="fw-bold fst-italic">(Đọc Qrcode nhanh và chính xác hơn)</span>',
            'value'         => array_key_first(EventSetting::getOptionsQrcodeCorrection()),
            'options'       => json_encode(EventSetting::getOptionsQrcodeCorrection()),
            'input_type'    => EventSetting::INPUT_TYPE_SELECT
        ],
        'qrcode_output' => [
            'name'          => 'QRCODE_OUTPUT',
            'description'   => "Định dạng đầu ra Qrcode",
            'value'         => array_key_first(EventSetting::getOptionsQrcodeOutput()),
            'options'       => json_encode(EventSetting::getOptionsQrcodeOutput()),
            'input_type'    => EventSetting::INPUT_TYPE_SELECT
        ],
    ],

    /****************************************** MOBILE ******************************************/

    'MOBILE' => [
        'allow_checkin_nodata' => [
            'name'          => 'ALLOW_CHECKIN_NODATA',
            'description'   => "Cho phép checkin không đầu vào",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_camera' => [
            'name'          => 'ALLOW_CHECKIN_CAMERA',
            'description'   => 'Cho phép checkin bằng camera <i class="fa-solid fa-camera"></i>',
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'no_duplicate_checkin'     => [
            'name'          => 'NO_DUPLICATE_CHECKIN',
            'description'   => "Kiểm tra checkin trùng qrcode",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH,
            'tutor'         => true,
        ],
        'allow_checkin_by_date'     => [
            'name'          => 'ALLOW_CHECKIN_BY_DATE',
            'description'   => "Kiểm tra checkin trùng theo ngày",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_by_user'     => [
            'name'          => 'ALLOW_CHECKIN_BY_USER',
            'description'   => "Kiểm tra checkin trùng theo tài khoản",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'show_checkin_count'     => [
            'name'          => 'SHOW_CHECKIN_COUNT',
            'description'   => "Hiển thị số lần checkin",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_playing_sound'     => [
            'name'          => 'ALLOW_CHECKIN_PLAYING_SOUND',
            'description'   => 'Checkin phát âm thanh <i class="fa fa-volume-up"></i>',
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        // 'allow_checkin_print' => [
        //     'name'          => 'ALLOW_CHECKIN_PRINT',
        //     'description'   => "Checkin in tem",
        //     'value'         => 0,
        //     'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        // ],
    ],

    // /****************************************** DESKTOP ******************************************/

    'DESKTOP' => [
        'allow_checkin_nodata' => [
            'name'          => 'ALLOW_CHECKIN_NODATA',
            'description'   => "Cho phép checkin không đầu vào",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_camera' => [
            'name'          => 'ALLOW_CHECKIN_CAMERA',
            'description'   => 'Cho phép checkin bằng camera <i class="fa-solid fa-camera"></i>',
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'no_duplicate_checkin'     => [
            'name'          => 'NO_DUPLICATE_CHECKIN',
            'description'   => "Kiểm tra checkin trùng qrcode",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH,
            'tutor'         => true,
        ],
        'allow_checkin_by_date'     => [
            'name'          => 'ALLOW_CHECKIN_BY_DATE',
            'description'   => "Kiểm tra checkin trùng theo ngày",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_by_user'     => [
            'name'          => 'ALLOW_CHECKIN_BY_USER',
            'description'   => "Kiểm tra checkin trùng theo tài khoản",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'show_checkin_count'     => [
            'name'          => 'SHOW_CHECKIN_COUNT',
            'description'   => "Hiển thị số lần checkin",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_playing_sound'     => [
            'name'          => 'ALLOW_CHECKIN_PLAYING_SOUND',
            'description'   => 'Checkin phát âm thanh <i class="fa fa-volume-up"></i>',
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
        'allow_checkin_print' => [
            'name'          => 'ALLOW_CHECKIN_PRINT',
            'description'   => "Checkin in tem",
            'value'         => 0,
            'input_type'    => EventSetting::INPUT_TYPE_SWITCH
        ],
    ],
];
