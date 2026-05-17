<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Phải chấp nhận :attribute.',
    'active_url' => ':attribute không phải là URL hợp lệ.',
    'after' => ':attribute phải là ngày sau :date.',
    'after_or_equal' => ':attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':attribute chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.',
    'alpha_name' => ':attribute phải có định dạng giống như tên mà không có ký tự đặc biệt hoặc dấu câu.',
    'alpha_num' => ':attribute chỉ được chứa chữ cái và số.',
    'array' => ':attribute phải là một mảng.',
    'before' => ':attribute phải là ngày trước :date.',
    'before_or_equal' => ':attribute phải là ngày trước hoặc bằng :date.',
    'between' => [
        'numeric' => ':attribute phải nằm trong khoảng :min đến :max.',
        'file' => ':attribute phải có kích thước từ :min đến :max kilobytes.',
        'string' => ':attribute phải có độ dài từ :min đến :max ký tự.',
        'array' => ':attribute phải có từ :min đến :max phần tử.',
    ],
    'boolean' => 'Trường :attribute phải là true hoặc false.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'date' => ':attribute không phải là ngày hợp lệ.',
    'date_format' => ':attribute không khớp với định dạng :format.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải có :digits chữ số.',
    'digits_between' => ':attribute phải có độ dài từ :min đến :max chữ số.',
    'dimensions' => ':attribute có kích thước hình ảnh không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị trùng lặp.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'exists' => ':attribute đã chọn không tồn tại.',
    'file' => ':attribute phải là một tập tin.',
    'filled' => 'Trường :attribute phải có giá trị.',
    'gt' => [
        'numeric' => ':attribute phải lớn hơn :value.',
        'file' => ':attribute phải lớn hơn :value kilobytes.',
        'string' => ':attribute phải dài hơn :value ký tự.',
        'array' => ':attribute phải có nhiều hơn :value phần tử.',
    ],
    'gte' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value kilobytes.',
        'string' => ':attribute phải dài hơn hoặc bằng :value ký tự.',
        'array' => ':attribute phải có :value phần tử hoặc nhiều hơn.',
    ],
    'image' => ':attribute phải là một hình ảnh.',
    'in' => ':attribute đã chọn không hợp lệ.',
    'in_array' => 'Trường :attribute không tồn tại trong :other.',
    'integer' => ':attribute phải là một số nguyên.',
    'ip' => ':attribute phải là địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là một chuỗi JSON hợp lệ.',
    'lt' => [
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'file' => ':attribute phải nhỏ hơn :value kilobytes.',
        'string' => ':attribute phải ngắn hơn :value ký tự.',
        'array' => ':attribute phải có ít hơn :value phần tử.',
    ],
    'lte' => [
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'file' => ':attribute phải nhỏ hơn hoặc bằng :value kilobytes.',
        'string' => ':attribute phải ngắn hơn hoặc bằng :value ký tự.',
        'array' => ':attribute không được có nhiều hơn :value phần tử.',
    ],
    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'file' => ':attribute không được lớn hơn :max kilobytes.',
        'string' => ':attribute không được dài hơn :max ký tự.',
        'array' => ':attribute không được có nhiều hơn :max phần tử.',
    ],
    'mimes' => ':attribute phải là một tập tin có kiểu: :values.',
    'mimetypes' => ':attribute phải là một tập tin có kiểu: :values.',
    'min' => [
        'numeric' => ':attribute phải ít nhất là :min.',
        'file' => ':attribute phải ít nhất là :min kilobytes.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
        'array' => ':attribute phải có ít nhất :min phần tử.',
    ],
    'not_in' => ':attribute đã chọn không hợp lệ.',
    'not_regex' => 'Định dạng :attribute không hợp lệ.',
    'numeric' => ':attribute phải là một số.',
    'present' => 'Trường :attribute phải có mặt.',
    'regex' => 'Định dạng :attribute không hợp lệ.',
    'required' => 'Trường :attribute là bắt buộc.',
    'required_if' => 'Trường :attribute là bắt buộc khi :other là :value.',
    'required_unless' => 'Trường :attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => 'Trường :attribute là bắt buộc khi :values có mặt.',
    'required_with_all' => 'Trường :attribute là bắt buộc khi :values có mặt.',
    'required_without' => 'Trường :attribute là bắt buộc khi :values không có mặt.',
    'required_without_all' => 'Trường :attribute là bắt buộc khi không có bất kỳ :values nào có mặt.',
    'same' => ':attribute và :other phải giống nhau.',
    'size' => [
        'numeric' => ':attribute phải là :size.',
        'file' => ':attribute phải có kích thước :size kilobytes.',
        'string' => ':attribute phải có độ dài :size ký tự.',
        'array' => ':attribute phải chứa :size phần tử.',
    ],
    'string' => ':attribute phải là một chuỗi.',
    'timezone' => ':attribute phải là một múi giờ hợp lệ.',
    'unique' => ':attribute đã có người dùng.',
    'uploaded' => ':attribute tải lên thất bại.',
    'url' => 'Định dạng :attribute không hợp lệ.',
    'can_be_author' => 'Người dùng đã chọn không thể là tác giả.',
    'current_password' => 'Mật khẩu hiện tại không hợp lệ.',
    'lowercase' => 'Phải viết thường',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'can_be_author' => [
            'accepted' => 'Selected author is invalid.',
        ],
        'current_password' => [
            'accepted' => 'The current password is invalid.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'code' => 'Mã',
        'name' => 'Tên',
        'title' => 'Tiêu đề',
        'username' => 'Tên người dùng',
        'email' => 'Email',
        'first_name' => 'Tên',
        'last_name' => 'Họ',
        'current_password' => 'Mật khẩu hiện tại',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'city' => 'Thành phố',
        'country' => 'Quốc gia',
        'address' => 'Địa chỉ',
        'phone' => 'Số điện thoại',
        'mobile' => 'Di động',
        'age' => 'Tuổi',
        'sex' => 'Giới tính',
        'gender' => 'Giới tính',
        'day' => 'Ngày',
        'month' => 'Tháng',
        'year' => 'Năm',
        'hour' => 'Giờ',
        'minute' => 'Phút',
        'second' => 'Giây',
        'title' => 'Tiêu đề',
        'content' => 'Nội dung',
        'description' => 'Mô tả',
        'excerpt' => 'Trích dẫn',
        'date' => 'Ngày',
        'time' => 'Thời gian',
        'available' => 'Sẵn có',
        'size' => 'Kích thước',
        'posted_at' => 'Đăng vào lúc',
        'author_id' => 'Tác giả',
        'post_id' => 'Sản phẩm',
        'thumbnail_id' => 'Ảnh đại diện',
        'product_ids' => 'Số lượng sản phẩm',

        'roles' => 'Vai trò/Phân quyền',
        'company_id'      => "Công ty",
        'province_id'     => "Tỉnh thành",
        'event_id'      => "Sự kiện",
        'event_code' => 'Mã sự kiện',
        'qrcode' => 'Mã qrcode',
        'status'        => "Trạng thái",
        'type'        => "Loại/Nhóm",
        'register_source' => 'Nguồn đăng ký',
        'field_date' => 'Trường thời gian',
        'from_date' => 'Ngày diễn ra',
        'to_date' => 'Ngày kết thúc',
        'lanugages' => 'Ngôn ngữ',
        'settings' => 'Cài đặt',
        'confirm' => 'Chuỗi xác nhận',
        'company_type' => 'Loại hình sự kiện',
        'expire_date' => 'Ngày hết hạn',
        'date' => 'Ngày hiện tại',
    ],

    'errors' => ':count lỗi :|:count lỗi : ',
];
