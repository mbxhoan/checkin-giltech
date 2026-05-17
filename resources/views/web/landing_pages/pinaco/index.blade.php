<!DOCTYPE html>
<html lang="vi">


<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thư mời sự kiện PINACO</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-custom-gradient {
            position: relative;
            isolation: isolate;
            background: linear-gradient(180deg, #03318dff 0%, #1155adff 50%, #02318dff 100%);
        }

        .bg-custom-gradient::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('{{ asset('images/hieu_ung_pinaco.png') }}') center top / cover no-repeat;
            opacity: 0.35;
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 0;
        }

        .btn-gradient {
            background: linear-gradient(to right, #174684, #447da9);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        /* Thanh cuộn */
        .content-area::-webkit-scrollbar {
            width: 5px;
        }

        .content-area::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 5px;
        }

        /* vien phan cach */
        .border-section {
            position: relative;
        }

        /* vech sang phan cach */
        .border-section::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #ffffff 50%, transparent 100%);
            box-shadow: 0 0 8px 2px rgba(78, 170, 240, 0.8),
                0 0 15px 4px rgba(42, 115, 212, 0.6);
            border-radius: 50%;
            z-index: 1;
        }

        .border-section:last-child::after {
            display: none;
        }

        .border-section:last-child {
            border-bottom: none;
        }

        /* Thanh menu */
        .nav-item {
            color: #002b7f;
            font-size: 10px;
            width: 25%;
            height: 100%;
            cursor: pointer;
            transition: 0.3s;
            border-top: 3px solid transparent;
        }

        .nav-item.active {
            color: #0d6efd;
            border-top: 3px solid #0d6efd;
            background-color: #f1f5f9;
        }

        .digit-box {
            width: 38px;
            height: 56px;
            background: linear-gradient(180deg, #53a3eb 0%, #1f64c6 100%);
            border: 1px solid #ffffff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 800;
            color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        /* slide ảnh */
        .swiper {
            width: 100%;
            padding-top: 30px;
            padding-bottom: 50px;
        }

        .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 130px;
            /* Độ rộng ảnh */
            height: 175px;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            /* Hiệu ứng mượt */
            opacity: 0.5;
            /* Ảnh 2 bên hơi mờ */
            transform: scale(0.8);
            /* Ảnh 2 bên nhỏ lại */
        }

        /* Khi ảnh nằm ở giữa (active) */
        .swiper-slide-active {
            opacity: 1;
            transform: scale(1.1);
            /* Phóng to ảnh ở giữa */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            z-index: 10;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .light-beam-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: linear-gradient(90deg,
                    rgba(78, 170, 240, 0) 0%,
                    rgba(78, 170, 240, 0.4) 30%,
                    rgba(78, 170, 240, 0.6) 50%,
                    rgba(78, 170, 240, 0.4) 70%,
                    rgba(78, 170, 240, 0) 100%);
            border-radius: 50%;
            transform: scaleY(0.7);
        }

        .light-beam-background::before,
        .light-beam-background::after {
            content: "";
            position: absolute;
            left: 0;
            width: 100%;
            height: 1px;
            background: #ffffff;
            z-index: 2;
            border-radius: 50%;
        }

        .light-beam-background::before {
            top: 5px;
            box-shadow:
                0 0 10px 4px rgba(255, 255, 255, 0.9),
                0 0 20px 8px rgba(78, 170, 240, 0.7);
        }

        /* Tinh chỉnh vệt dưới */
        .light-beam-background::after {
            bottom: 5px;
            box-shadow:
                0 0 10px 4px rgba(255, 255, 255, 0.9),
                0 0 20px 8px rgba(42, 115, 212, 0.6);
        }

        /* xac nhan tham gia */
        .confirm-option {
            cursor: pointer;
            width: 130px;
        }

        .confirm-option input {
            display: none;
        }

        .option-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 11px 6px;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 11px;
            font-weight: bold;
        }

        .confirm-option input:checked+.option-card {
            background: white;
            color: #003366;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .text-justify-custom {
            text-align: justify;
        }

        #section-welcome {
            background-image: url('https://checkin.giltech.com.vn/storage/medias/696/nền-trơn.png');
            background-repeat: no-repeat;
            background-position: bottom center;
            background-size: cover;
        }
    </style>
</head>

<body class="bg-light text-white d-flex justify-content-center m-0 p-0">

    <div class="position-relative shadow-lg d-flex flex-column overflow-hidden bg-custom-gradient"
        style="width: 100%; max-width: 450px; height: 100vh; height: 100dvh; z-index: 100;">

        <div id="section-welcome"
            class="position-absolute top-0 start-0 w-100 d-flex flex-column justify-content-center align-items-center text-center px-3"
            style="
                height: calc(100% - 65px);
                z-index: 100;
            ">

            <img src="https://checkin.giltech.com.vn/storage/medias/687/LOGO.png" alt="Logo PINACO" style="height: 110px;"
                class="mb-5">

            <p class="mt-3 mb-1 fw-bold" style="white-space: nowrap; font-size: 13px;">CÔNG TY CỔ PHẦN PIN ẮC QUY MIỀN NAM (PINACO)</p>
            <p class="mb-4" style="font-size: 13px;">xin trân trọng kính mời</p>

            <h2 class="fw-normal mb-2 fs-3">
                {{ $client->custom_fields['title'] ?? '' }} <span class="fw-bold text-uppercase">{{ $client->name ?? '' }}</span>
            </h2>
            <h6 class="fw-bold text-uppercase mb-1" style="font-size: 14px;">{{ $client->custom_fields['position'] ?? ''}}</h6>
            <h6 class="fw-bold text-uppercase mb-4" style="font-size: 14px;">{{ $client->custom_fields['company'] ?? ''}}</h6>

            <p class="mb-5" style="font-size: 13px;">đến tham dự <strong>Lễ Kỷ niệm 50 năm thành lập</strong></p>

            <button class="btn btn-gradient text-white rounded-3 px-5 py-2 fw-bold border border-white shadow"
                onclick="openInvitation()">
                MỞ THƯ MỜI
            </button>

        </div>

        <div class="flex-grow-1 overflow-y-auto content-area position-relative d-none" id="main-content"
            style="scroll-behavior: smooth; padding-bottom: 65px;">

            <div id="home" class="border-section scroll-section">
                <img src="https://checkin.giltech.com.vn/storage/medias/664/KV.png" alt="Key Visual PINACO"
                    class="w-100 object-fit-cover mb-4">

                <div class="px-3 pb-2">
                    <p class="mb-4 text-justify-custom" style="font-size: 12px; line-height: 1.6;">
                        Ngày 19/04/2026 đánh dấu một cột mốc lịch sử vĩ đại: PINACO chính thức trọn nửa thế kỷ hình thành và phát triển. Hành trình 50 năm qua là một bản hùng ca được viết lên bởi bản lĩnh kiên cường, ý chí sắt đá và khát vọng vươn lên không ngừng nghỉ. Từ một doanh nghiệp nhỏ bé trong những ngày đầu sau giải phóng, PINACO đã bứt phá mạnh mẽ, vươn mình trở thành Nhà sản xuất Pin và Ắc quy hàng đầu Việt Nam. Mỗi thành tựu của ngày hôm nay chính là minh chứng sắc bén cho chất lượng, uy tín thương hiệu, và đặc biệt là sự cống hiến đầy tâm huyết của lớp lớp thế hệ cán bộ nhân viên.
                    </p>

                    <div class="position-relative mb-4 px-3">

                        <!-- Ảnh hiệu ứng -->
                        <!-- <img src="https://checkin.giltech.com.vn/storage/medias/691/typo-light-1.png"
                            class="position-absolute h-100"
                            style="top: 10px; width: 125%; left: -41%; object-fit: contain; z-index: 2; pointer-events: none;"> -->

                        <!-- Ảnh chính -->
                        <img src="https://checkin.giltech.com.vn/storage/medias/702/text-(1).png"
                            alt="Key Visual PINACO"
                            class="w-100 object-fit-cover"
                            style="position: relative; z-index: 1;">

                    </div>
                    <p class="mb-4 text-justify-custom" style="font-size: 12px; line-height: 1.6;">
                        Mang trong mình niềm tự hào của di sản nửa thế kỷ, PINACO bước vào kỷ nguyên mới với tâm thế vững vàng và tinh thần tiên phong. Bằng nội lực mạnh mẽ và sự đồng lòng của một tập thể gắn kết, chúng ta cam kết sẽ tạo nên những bước bứt phá ngoạn mục trong năm 2026, tiếp tục chinh phục những đỉnh cao mới và khẳng định tầm vóc PINACO trên chặng đường hướng tới tương lai.
                    </p>


                </div>
            </div>

            <div id="info" class="p-3 border-section scroll-section">
                <div class="text-center mt-2">
                    <div class="text-center mt-2">
                        <div id="cd-title" class="mb-1">
                            <img src="https://checkin.giltech.com.vn/storage/medias/679/Pinaco-microsite-04.png"
                                alt="Sự kiện sẽ bắt đầu sau" style="height: 65px; object-fit: contain;">
                        </div>

                        <div class="d-flex justify-content-center gap-3 align-items-start ">
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 align-items-start mb-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-1 fw-bold text-white fs-6">Ngày</div>
                            <div class="d-flex gap-1" id="cd-days">
                                <div class="digit-box">0</div>
                                <div class="digit-box">0</div>
                            </div>
                        </div>

                        <div class="text-white fw-bold fs-2" style="margin-top: 24px;">:</div>

                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-1 fw-bold text-white fs-6">Giờ</div>
                            <div class="d-flex gap-1" id="cd-hours">
                                <div class="digit-box">0</div>
                                <div class="digit-box">0</div>
                            </div>
                        </div>

                        <div class="text-white fw-bold fs-2" style="margin-top: 24px;">:</div>

                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-1 fw-bold text-white fs-6">Phút</div>
                            <div class="d-flex gap-1" id="cd-minutes">
                                <div class="digit-box">0</div>
                                <div class="digit-box">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 align-items-center">
                    <div class="col-6 position-relative" style="height: 190px;">

                        <img src="https://checkin.giltech.com.vn/storage/medias/691/typo-light-1.png"
                            alt="Hiệu ứng nền"
                            class="position-absolute w-100 h-100"
                            style="top: 0; left: 0; z-index: 0; object-fit: contain; opacity: 0.8;"> <img src="https://checkin.giltech.com.vn/storage/medias/666/white-palace__1_-removebg-preview.png"
                            alt="Tòa nhà tổ chức"
                            class="w-00 h-100 rounded object-fit-cover position-absolute"
                            style="top: 0; left: 13px; z-index: 1;">

                        <a href="https://www.google.com/maps/search/?api=1&query=White+Palace+Võ+Văn+Kiệt"
                            target="_blank"
                            class="d-flex align-items-center justify-content-center text-decoration-none text-white fw-bold position-absolute"
                            style="background: linear-gradient(to right, #4eaaf0, #2a73d4);
                                height: 20px;
                                width: fit-content;
                                padding: 0 19px;
                                bottom: 5px;
                                left: 25px;
                                border-radius: 15px 15px 0 0;
                                font-size: 9px;
                                letter-spacing: 0.5px;
                                border-bottom: 2px solid #ffffff;
                                z-index: 2;"> XEM BẢN ĐỒ
                        </a>
                    </div>
                    <div class="col-6 text-white">
                        <div class="mb-2">
                            <div class="fw-bold mb-1"
                                style="background: linear-gradient(90deg, #6ec8ff 20%, transparent 100%); padding: 4px 12px; font-size: 14px; width: 130px;">
                                Thời gian
                            </div>
                            <div style="line-height: 1.6;">
                                <div class="" style="font-size: 11px;"><b>16:30</b> Chủ nhật, ngày 19/04/2026</div>
                            </div>
                        </div>

                        <div>
                            <div class="fw-bold mb-1"
                                style="background: linear-gradient(90deg, #6ec8ff 20%, transparent 100%); padding: 4px 12px; font-size: 14px; width: 130px;">
                                Địa điểm
                            </div>
                            <div style="font-size: 12px; line-height: 1.5;">
                                <div class="fw-bold mb-1" style="font-size: 11px; text-transform: uppercase;">
                                    WHITE PALACE VÕ VĂN KIỆT
                                </div>
                                <div style="font-size: 11px; line-height: 1.5;" class="text-white">
                                    <div class="mb-2">
                                        <!-- <u style="text-underline-offset: 3px;">ĐỊA CHỈ CŨ:</u><br> -->
                                        59 Võ Văn Kiệt, Phường An Lạc,<br>
                                        TP. Hồ Chí Minh
                                    </div>

                                    <div>
                                        <!-- <u style="text-underline-offset: 3px;">ĐỊA CHỈ MỚI:</u><br> -->
                                        (59 Võ Văn Kiệt, Phường An Lạc,<br>
                                        Quận Bình Tân,TP. Hồ Chí Minh)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-white text-center">
                        <img src="https://checkin.giltech.com.vn/storage/medias/682/Pinaco-microsite-03.png"
                            alt="Chương trình" class="mb-2" style="height: 70px; object-fit: contain;">

                        <div class="p-2 rounded-4 mx-auto text-start " style="background-color: #1c4598; max-width: 480px; font-size: 12px; font-weight: 400; line-height: 1.6;">

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">16h30 - 18h00</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    <div class="mb-1">Đón khách, trò chơi tương tác,</div>
                                    <div>chụp hình Glambot, triễn lãm dấu ấn - thành tựu</div>

                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">18h00 - 18h25</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Lễ Khai mạc
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">18h25 - 18h30</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Phim "Dấu ấn hành trình 50 năm"
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">18h30 - 18h55</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Tri ân người lao động các thế hệ
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">18h55 - 19h00</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Nghi thức kỷ niệm 50 năm
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">19h00 - 19h05</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Khai tiệc
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">19h35 - 19h45</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Giới thiệu sản phẩm mới S76
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">17h45 - 21h15</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    <div class="mb-1">Tiếc mục biểu diễn: Ca sĩ Dương Hoàng Yến</div>
                                    <div>Quay số trúng thưởng</div>

                                </div>
                            </div>

                            <!-- <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">19:45</div>
                                <div class="position-relative ps-3 " style="border-left: 1.5px solid rgba(255, 255, 255, 0.7);">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Quay số trúng thưởng
                                </div>
                            </div> -->

                            <div class="d-flex align-items-start">
                                <div style="width: 95px;" class="flex-shrink-0">21:15 - 21h30</div>
                                <div class="position-relative ps-3" style="border-left: 1.5px solid rgba(255, 255, 255, 0.7); padding-bottom: 5px;">
                                    <div class="position-absolute bg-white rounded-circle" style="left: -4.5px; top: 6px; width: 8px; height: 8px;"></div>
                                    Tặng quà lưu niệm
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="https://checkin.giltech.com.vn/storage/medias/725/cổng-1404.jpg"
                                    alt="Sự kiện">
                            </div>
                            <div class="swiper-slide">
                                <img src="https://checkin.giltech.com.vn/storage/medias/708/Poster-DHY-demo.png"
                                    alt="Sự kiện">
                            </div>
                            <div class="swiper-slide">
                                <img src="https://checkin.giltech.com.vn/storage/medias/705/Poster-Tuyen-Tang-demo.png"
                                    alt="Sự kiện">
                            </div>
                            <div class="swiper-slide">
                                <img src="https://checkin.giltech.com.vn/storage/medias/706/Poster-Vinh-Phu-demo.png"
                                    alt="Sự kiện">
                            </div>
                            <div class="swiper-slide">
                                <img src="https://checkin.giltech.com.vn/storage/medias/714/SÂN-KHẤU_09042026.jpg"
                                    alt="Sự kiện">
                            </div>
                            <!-- <div class="swiper-slide">
                                <img src="https://giltech.com.vn/storage/uploads/danhmuc/may-pos-tinh-tien-23-typeicon.jpg"
                                    alt="Sự kiện">
                            </div> -->
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>

            <div id="qrcode" class="p-3 border-section scroll-section text-center">
                <img src="https://checkin.giltech.com.vn/storage/medias/676/Pinaco-microsite-03.png" alt="Quét mã check-in"
                    class="mb-1" style="height: 70px; object-fit: contain;">

                <div class="bg-white p-2 border rounded shadow-sm d-inline-block mb-3">
                    <img src="{{ $client->getImgQrcode(true) }}" alt="{{ $client->qrcode }}" width="180" height="180">
                </div>

                <h5 class="mb-1">{{ $client->custom_fields['title'] ?? '' }} <span class="fw-bold text-uppercase">{{ $client->name ?? '' }}</span></h5>
                <div class="small fw-bold text-uppercase">{{ $client->custom_fields['position'] ?? '' }}</div>
                <div class="small fw-bold text-uppercase">{{ $client->custom_fields['company'] ?? '' }}</div>

                <div class="mt-4 p-2 rounded text-center">
                    <img src="https://checkin.giltech.com.vn/storage/medias/680/Pinaco-microsite-06.png"
                        alt="Lưu ý quan trọng khi tham dự sự kiện" class="mb-1"
                        style="height: 50px; object-fit: contain;">
                    <div class="fst-italic text-white text-center px-2" style="font-size: 11px; line-height: 1.6;">
                        <div>
                            Mỗi mã QR chỉ áp dụng cho 01 khách mời duy nhất.<br>
                            Quý khách vui lòng không chia sẻ hay chuyển cho người khác
                            để đảm bảo thông tin được bảo mật và quá trình đón khách (check-in) diễn ra thuận lợi.
                        </div>
                        <div class="mt-2">
                            Vui lòng lưu giữ mã QR bằng cách:
                        </div>

                        <div class="mb-1" style="display: inline-block;">
                            • Truy cập lại Email để mở trang microsite.<br>
                            • Chụp màn hình mã QRcode để sử dụng trong sự kiện.
                        </div>
                    </div>
                </div>
            </div>

            <div id="seating" class="p-3 border-section scroll-section text-center" style="min-height: 80vh;">
                <img src="https://checkin.giltech.com.vn/storage/medias/678/Pinaco-microsite-05.png" alt="Sơ đồ chỗ ngồi"
                    class="mb-1" style="height: 70px; object-fit: contain;">
                <div>
                    <div class="small fw-bold text-uppercase">Nhóm khách: {{ $client->custom_fields['type_guest'] ??
                        '' }}</div>
                    <div class="small fw-bold text-uppercase">Bàn số: {{ $client->custom_fields['table_number'] ?? '' }}
                    </div>
                </div>
                <div class="d-flex justify-content-center mb-4 mt-3">
                    <img src="https://checkin.giltech.com.vn/storage/medias/735/cho_ngoi_update.jpg"
                        class="img-fluid rounded border"
                        alt="Sơ đồ chỗ ngồi">
                </div>

                <fieldset class="border border-white rounded px-2 pb-2 pt-2 mb-3 w-100"
                    style="border-width: 1px !important;">
                    <legend class="float-none w-auto px-2 mx-auto position-relative text-center mb-3"
                        style="color: #ffffff;">

                        <div class="position-absolute top-50 start-50 translate-middle"
                            style="width: 150%; height: 50px; background: radial-gradient(ellipse at center, rgba(78, 170, 240, 0.7) 0%, transparent 70%); z-index: 1;">
                        </div>

                        <img src="https://checkin.giltech.com.vn/storage/medias/686/Pinaco-microsite-04.png"
                            alt="Những điều cần lưu ý" class="position-relative"
                            style="height: 30px; object-fit: contain; z-index: 2; filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));">
                    </legend>

                    <div style="font-size: 12px; line-height: 1.6;" class="text-white">
                        <!-- <div class="mb-2 text-center">
                            <strong style="font-size: 12px;">1. Trang phục</strong><br>
                            Quý khách vui lòng lựa chọn trang phục <strong>lịch sự,<br>trang trọng</strong> để phù hợp
                            với tính chất<br>của sự kiện kỷ niệm.
                        </div> -->
                        <div class="mb-2 text-center">
                            <strong style="font-size: 12px;">1. Thời gian tham dự</strong><br>
                            Thời gian đón khách bắt đầu từ <strong>16:30</strong>.<br>
                            Quý khách vui lòng đến <strong>đúng giờ</strong> theo thư mời để<br>thuận tiện cho việc đón
                            tiếp và sắp xếp chỗ ngồi.
                        </div>
                        <div class="mb-2 text-center">
                            <strong style="font-size: 12px;">2. Check-in sự kiện</strong><br>
                            Vui lòng <strong>chụp lại mã QR cá nhân</strong> để tiện cho việc <br><strong>check-in trong tại sự kiện</strong>.<br>
                            Khách hàng đến <strong>tham dự sự kiện và check-in mã<br>
                            QR thành công</strong> sẽ có <strong>cơ hội tham gia quay số<br>
                            trúng thưởng</strong> với những phần quà hấp dẫn trong<br> chương trình.
                        </div>
                        <div class="mb-2 text-center">
                            <strong style="font-size: 12px;">3. Tương tác & ghi hình</strong><br>
                            Sự kiện có thể được <strong>ghi hình, chụp ảnh</strong>.<br>
                            Việc tham dự đồng nghĩa với việc Quý khách đồng<br>ý sử dụng hình ảnh cho mục đích truyền
                            thông.
                        </div>
                        <div class="mb-2 text-center">
                            <strong style="font-size: 12px;">4. Bảo quản tài sản cá nhân</strong><br>
                            Quý khách vui lòng tự bảo quản tư trang<br>trong suốt thời gian tham dự.
                        </div>
                        <!-- <div class="text-center">
                            <strong style="font-size: 12px;">6. An toàn & văn minh</strong><br>
                            Không mang vào khu vực sự kiện các vật dụng<br>nguy hiểm, chất kích thích hoặc gây ảnh
                            hưởng<br>đến người xung quanh.
                        </div> -->
                    </div>
                </fieldset>


                <!-- <div class="mt-4 p-3 rounded text-center">
                    <img src="https://checkin.giltech.com.vn/storage/medias/685/Pinaco-microsite-05.png"
                        alt="Theo dỗi thông tin kỹ niệm 50 năm thành lập Pinaco" class="mb-1"
                        style="height: 45px; object-fit: contain;">
                    <a href="https://ngoinha.pinaco.com/" target="_blank" class="text-decoration-none">
                        <button
                            class="btn btn-gradient text-white rounded-3 px-5 py-2 fw-bold border border-white shadow">
                            LANDING PAGE
                        </button>
                    </a>
                </div> -->

                <div class="mt-4 p-3 text-center position-relative" style="margin: auto;">
                    <div class="p-3 pt-4 pb-4 rounded-4 position-relative" style="background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1;">
                        <div class="mb-3 fw-bold text-uppercase" style="color: #0b2b6d; font-size: 13px; letter-spacing: 1px;">
                            THEO DÕI THÔNG TIN <br>
                            KỶ NIỆM 50 NĂM THÀNH LẬP PINACO
                        </div>
                    </div>

                    <a href="https://ngoinha.pinaco.com/" target="_blank" class="text-decoration-none"
                    style="display: inline-block; transform: translateY(-30px); position: relative; z-index: 10;">

                        <button class="btn text-white rounded-3 px-5 py-2 fw-bold border border-white shadow"
                            style="background: linear-gradient(to right, #0b2b6d 0%, #4a9dec 100%);">
                            LANDING PAGE
                        </button>
                    </a>
                </div>

                <!-- <div class="mt-1 p-3 rounded text-center">
                    <img src="https://checkin.giltech.com.vn/storage/medias/680/Pinaco-microsite-06.png"
                        alt="Lưu ý quan trọng khi tham dự sự kiện" class="mb-1"
                        style="height: 80px; object-fit: contain;">

                    <a href="URL_CUA_BAN_O_DAY" target="_blank" class="text-decoration-none">
                        <button
                            class="btn btn-gradient text-white rounded-3 px-5 py-2 fw-bold border border-white shadow">
                            HÌNH ẢNH CÁ NHÂN
                        </button>
                    </a>
                </div> -->

                <!-- <div class="mt-1 p-3 rounded text-center">
                    <div class="small text-uppercase mb-3 text-white" style="font-size: 12px; letter-spacing: 1px;">
                        ** Cách sử dụng chức năng lọc hình ảnh sau sự kiện:
                    </div>

                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);">
                        <iframe
                            src="https://www.youtube.com/embed/5rVN4N7GHUY?rel=0&modestbranding=1"
                            style="position: absolute; top:0; left:0; width:100%; height:100%; border:0;"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div> -->

                <div id="confirmation-box" class="mt-4 p-2 rounded-4 text-center mx-3"
                    style="background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); transition: all 0.5s ease;">

                    @if(!isset($client->custom_fields['attend']))
                    <div id="confirm-form">
                        <div class="text-center mb-2">
                            <img src="https://checkin.giltech.com.vn/storage/medias/723/Pinaco-microsite.png"
                                alt="Khảo sát"
                                style="height: 38px; object-fit: contain;">
                        </div>

                        <div class="mb-3 fw-bold text-uppercase" style="color: #0b2b6d; font-size: 10px;">
                            QUÝ KHÁCH VUI LÒNG XÁC NHẬN THAM GIA<br>
                            ĐỂ PINACO CÓ SỰ CHUẨN BỊ ĐÓN TIẾP TỐT NHẤT
                        </div>

                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <button type="button" class="btn btn-gradient text-white rounded-3 py-2 fw-bold border border-white shadow-sm w-100"
                                style="max-width: 250px; font-size: 11px; white-space: nowrap;"
                                onclick="submitConfirmation('yes', this)">
                                THAM GIA
                            </button>
                            <button type="button" class="btn btn-gradient text-white rounded-3 py-2 fw-bold border border-white shadow-sm w-100"
                                style="max-width: 250px; font-size: 11px; white-space: nowrap;"
                                onclick="submitConfirmation('no', this)">
                                KHÔNG THAM GIA
                            </button>
                            <!-- <label class="confirm-option m-0" style="cursor: pointer;">
                                <input type="radio" name="attendance" value="yes" checked>
                                <div class="btn btn-gradient text-white rounded-3 py-2 fw-bold border border-white shadow-sm w-100"
                                    style="max-width: 250px; font-size: 11px; white-space: nowrap;">
                                    THAM GIA
                                </div>
                            </label> -->

                            <!-- <label class="confirm-option m-0" style="cursor: pointer;">
                                <input type="radio" name="attendance" value="no">
                                <div class="btn btn-gradient text-white rounded-3 py-2 fw-bold border border-white shadow-sm w-100 "
                                    style="max-width: 250px; font-size: 11px; white-space: nowrap;">
                                    KHÔNG THAM GIA
                                </div>
                            </label> -->
                        </div>

                        <!-- <button class="btn btn-gradient text-white rounded-3 px-5 py-2 fw-bold border border-white shadow-sm w-100"
                            style="max-width: 250px; font-size: 12px;"
                            onclick="submitConfirmation()">
                            GỬI XÁC NHẬN
                        </button> -->

                        <div class="mt-2" style="color: #0b2b6d; font-size: 10px;">
                            Thông tin cá nhân sẽ được ghi nhận theo thư mời.<br>
                            Xin cảm ơn.
                        </div>
                    </div>

                    <div id="thank-you-msg" style="display: none;">
                        <div class="py-3">
                            <div class="mb-2 fw-bold text-uppercase" style="color: #0b2b6d; font-size: 12px;">CẢM ƠN QUÝ KHÁCH!</div>
                            <p id="msg-detail" class="small mb-0" style="color: #0b2b6d; font-size: 11px;"></p>
                        </div>
                    </div>
                    @else
                    <div id="thank-you-msg">
                        <div class="py-3">
                            <div class="mb-2 fw-bold text-uppercase" style="color: #0b2b6d; font-size: 12px;">CẢM ƠN QUÝ KHÁCH!</div>
                            <p id="msg-detail" class="small mb-0" style="color: #0b2b6d; font-size: 11px;">
                                @if($client->custom_fields['attend'] === 'yes')
                                Pinaco đã ghi nhận sự hiện diện của Quý khách.<br>Hẹn gặp lại Quý khách tại buổi lễ!
                                @else
                                Pinaco đã ghi nhận thông tin.<br>Rất tiếc vì không thể đón tiếp Quý khách lần này.
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="text-center mt-4 position-relative" style="padding: 25px 0; margin-bottom: 20px;">
                    <div class="light-beam-background"></div>

                    <h6 class="fw-bold text-uppercase position-relative text-white"
                        style="font-size: 13px; line-height: 1.6; z-index: 3; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Hân hạnh được đón tiếp Quý khách<br>tại Lễ kỷ niệm 50 năm thành lập của PINACO
                    </h6>
                </div>
            </div>
        </div>

        <div class="position-absolute bottom-0 start-0 w-100 bg-white border-top shadow-lg d-flex justify-content-around align-items-center z-3"
            style="height: 65px;" id="bottom-nav">
            <div class="nav-item d-flex flex-column align-items-center justify-content-center fw-semibold"
                data-target="home" onclick="scrollToSection('home')">
                <i class="fa-solid fa-house fs-5 mb-1"></i> Trang chủ
            </div>
            <div class="nav-item d-flex flex-column align-items-center justify-content-center fw-semibold"
                data-target="info" onclick="scrollToSection('info')">
                <i class="fa-solid fa-calendar-alt fs-5 mb-1"></i> TT Sự kiện
            </div>
            <div class="nav-item d-flex flex-column align-items-center justify-content-center fw-semibold"
                data-target="qrcode" onclick="scrollToSection('qrcode')">
                <i class="fa-solid fa-qrcode fs-5 mb-1"></i> Mã check-in
            </div>
            <div class="nav-item d-flex flex-column align-items-center justify-content-center fw-semibold"
                data-target="seating" onclick="scrollToSection('seating')">
                <i class="fa-solid fa-chair fs-5 mb-1"></i> Sơ đồ chỗ ngồi
            </div>
            <!-- <div class="nav-item d-flex flex-column align-items-center justify-content-center fw-semibold"
                data-target="seating" onclick="scrollToSection('seating')">

                <svg width="28" height="28" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-1">
                    <circle cx="60" cy="60" r="22" fill="#1e40af"/>

                    <circle cx="60" cy="18" r="8" fill="#1e40af"/>
                    <circle cx="88" cy="27" r="8" fill="#1e40af"/>
                    <circle cx="102" cy="48" r="8" fill="#1e40af"/>
                    <circle cx="102" cy="72" r="8" fill="#1e40af"/>
                    <circle cx="88" cy="93" r="8" fill="#1e40af"/>
                    <circle cx="60" cy="102" r="8" fill="#1e40af"/>
                    <circle cx="32" cy="93" r="8" fill="#1e40af"/>
                    <circle cx="18" cy="72" r="8" fill="#1e40af"/>
                    <circle cx="18" cy="48" r="8" fill="#1e40af"/>
                    <circle cx="32" cy="27" r="8" fill="#1e40af"/>
                    <circle cx="60" cy="18" r="8" fill="#1e40af"/>
                </svg>

                Sơ đồ chỗ ngồi
            </div> -->
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const contentArea = document.getElementById('main-content');
        const sections = document.querySelectorAll('.scroll-section');
        const navItems = document.querySelectorAll('.nav-item');

        function openInvitation() {
            const welcomeScreen = document.getElementById('section-welcome');
            welcomeScreen.classList.remove('d-flex');
            welcomeScreen.classList.add('d-none');

            contentArea.classList.remove('d-none');

            if (!document.querySelector('.nav-item.active')) {
                document.querySelector('[data-target="home"]').classList.add('active');
            }
        }

        function scrollToSection(targetId) {
            const welcomeScreen = document.getElementById('section-welcome');

            if (!welcomeScreen.classList.contains('d-none')) {
                openInvitation();
            }

            // Đợi 50ms để giao diện mới kịp render, sau đó mới cuộn
            setTimeout(() => {
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    contentArea.scrollTo({
                        top: targetElement.offsetTop,
                        behavior: 'smooth'
                    });
                }
            }, 50);
        }

        contentArea.addEventListener('scroll', () => {
            let currentSectionId = '';
            sections.forEach(section => {
                if (contentArea.scrollTop >= section.offsetTop - 150) {
                    currentSectionId = section.getAttribute('id');
                }
            });
            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-target') === currentSectionId) {
                    item.classList.add('active');
                }
            });
        });

        const eventDate = new Date("2026-04-19T16:30:00").getTime();
        const countdownTimer = setInterval(() => {
            const distance = eventDate - new Date().getTime();

            let d = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            let h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            let m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');

            if (distance >= 0) {
                document.getElementById("cd-days").innerHTML = `<div class="digit-box">${d[0]}</div><div class="digit-box">${d[1]}</div>`;
                document.getElementById("cd-hours").innerHTML = `<div class="digit-box">${h[0]}</div><div class="digit-box">${h[1]}</div>`;
                document.getElementById("cd-minutes").innerHTML = `<div class="digit-box">${m[0]}</div><div class="digit-box">${m[1]}</div>`;
            } else {
                clearInterval(countdownTimer);
                document.getElementById("cd-days").innerHTML = `<div class="digit-box">0</div><div class="digit-box">0</div>`;
                document.getElementById("cd-hours").innerHTML = `<div class="digit-box">0</div><div class="digit-box">0</div>`;
                document.getElementById("cd-minutes").innerHTML = `<div class="digit-box">0</div><div class="digit-box">0</div>`;

                const cdTitle = document.getElementById("cd-title");
                cdTitle.innerText = "SỰ KIỆN ĐANG DIỄN RA";
                cdTitle.classList.add("text-danger");
            }
        }, 1000);

        var swiper = new Swiper(".mySwiper", {
            effect: "coverflow", // Hiệu ứng xoay 3D nhẹ
            grabCursor: true, // Hiện bàn tay khi kéo
            centeredSlides: true, // Giữ ảnh active ở giữa
            slidesPerView: "auto", // Tự động tính số lượng ảnh hiển thị
            spaceBetween: 20,
            loop: true, // Lặp lại vô tận
            coverflowEffect: {
                rotate: 0, // Độ nghiêng (để 0 cho phẳng)
                stretch: 0,
                depth: 100, // Độ sâu 3D
                modifier: 2,
                slideShadows: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            autoplay: {
                delay: 2500, // Tự động chuyển sau 2.5s
                disableOnInteraction: false,
            },
        });

        function submitConfirmation(status, element) {
            // const status = document.querySelector('input[name="attendance"]:checked').value;
            const form = document.getElementById('confirm-form');
            const msg = document.getElementById('thank-you-msg');
            const detail = document.getElementById('msg-detail');
            const submitBtn = form.querySelector('button');

            // Hiển thị trạng thái đang gửi
            // submitBtn.disabled = true;
            // submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ĐANG GỬI...';
            const buttons = form.querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = true);
            const originalText = element.innerHTML;
            element.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ĐANG GỬI...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route("clients.microsite.pinaco.attendance", ["eventCode" => $client->event_code, "token" => $client->custom_fields["token"]]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        attendance: status
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Ẩn form bằng hiệu ứng mờ dần
                        form.style.opacity = '0';

                        setTimeout(() => {
                            form.style.display = 'none';
                            msg.style.display = 'block';

                            if (status === 'yes') {
                                detail.innerHTML = "Pinaco đã ghi nhận sự hiện diện của Quý khách.<br>Hẹn gặp lại Quý khách tại buổi lễ!";
                            } else {
                                detail.innerHTML = "Pinaco đã ghi nhận thông tin.<br>Rất tiếc vì không thể đón tiếp Quý khách lần này.";
                            }
                        }, 400);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        button.forEach(btn => btn.disabled = false);
                        button.forEach(btn => btn.innerHTML = originalText);
                        // submitBtn.disabled = false;
                        // submitBtn.innerHTML = 'GỬI XÁC NHẬN';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Có lỗi xảy ra, vui lòng thử lại.');
                    button.forEach(btn => btn.disabled = false);
                    button.forEach(btn => btn.innerHTML = originalText);
                    // submitBtn.disabled = false;
                    // submitBtn.innerHTML = 'GỬI XÁC NHẬN';
                });
        }
    </script>
</body>

</html>
