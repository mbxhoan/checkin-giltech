<!DOCTYPE html>
<html lang="en">
    @php
        $page = config('app-setting.page');
    @endphp

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            Lucky Draw - Raffle | {{ config('app.name', 'Giltech Solutions') }}
        </title>
        <link href="{{ asset('argon') }}/img/brand/favicon.png" rel="icon" type="image/png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
            integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
            integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

        <link rel="stylesheet" href="{{ asset('css/lucky-draw/customs/test.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lucky-draw/components/coin.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lucky-draw/components/glow.css') }}">

        <link rel="preload" href="https://ck.giltech.com.vn/file/access/95" as="image">
        <link rel="preload" href="https://ck.giltech.com.vn/file/access/94" as="image">

        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/js/all.min.js"
            integrity="sha512-naukR7I+Nk6gp7p5TMA4ycgfxaZBJ7MO5iC3Fp6ySQyKFHOGfpkSZkYVWV5R7u7cfAicxanwYQ5D1e17EfJcMA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous">
        </script>

        <style>
            html, body {
                height: 100%;
                margin: 0;
                overflow: hidden;
            }

            @font-face {
                font-family: 'Metropolis';
                src: url('/assets/fonts/Metropolis/Metropolis-Bold.otf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            @font-face {
                font-family: 'Metropolis';
                src: url('/assets/fonts/Metropolis/Metropolis-Medium.otf') format('truetype');
                font-weight: bold;
                font-style: normal;
            }

            * {
                font-family: 'Metropolis', sans-serif !important;
            }

            /* Container chính */
            .raffle-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-top: -35vh;
            }

            /* Không hiển thị tên giải */
            .prize-name {
                display: none !important;
            }

            /* Container chung cho raffle box (fallback cho value khác 1,2,3,10) */
            #boxContainer {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: center;
                align-items: center;
                min-height: auto !important;
                max-width: 100%;
                width: 100%;
                padding: 0 20px;
                box-sizing: border-box;
                gap: 15px;
            }

            /* Ô (box) chung - nền trắng, viền tối như thiết kế */
            .raffle-box {
                border: 1px solid #333;
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }

            /* Khi đã có kết quả: click vào ô để quay lại (thay người vắng) */
            #boxContainer.raffle-result-visible .raffle-box.winner {
                cursor: pointer;
            }
            #boxContainer.raffle-result-visible .raffle-box.winner:hover {
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
            }

            /* Giải nhất (VALUE=1): 1 ô căn giữa, dịch sang trái một chút */
            #boxContainer.raffle-layout-value-1 {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: center;
                align-items: center;
                transform: translateX(-25px);
            }
            #boxContainer.raffle-layout-value-1 .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px 20px;
                flex: 0 0 auto;
                min-width: 0;
                max-width: 400px;
            }

            /* Giải nhì (VALUE=2): 1 hàng ngang 2 ô */
            #boxContainer.raffle-layout-value-2 {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: center;
                align-items: center;
            }
            #boxContainer.raffle-layout-value-2 .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 15px 16px;
                flex: 1 1 0;
                min-width: 150px;
            }

            /* Giải ba (VALUE=3): 1 hàng ngang 3 ô */
            #boxContainer.raffle-layout-value-3 {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: center;
                align-items: center;
            }
            #boxContainer.raffle-layout-value-3 .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 15px 20px;
                flex: 1 1 0;
                min-width: 250px;
            }

            /* Giải khuyến khích (VALUE=10): hàng trên 5 người, hàng dưới 5 người; ô rộng hơn để hiển thị full email */
            #boxContainer.raffle-layout-value-10 {
                display: grid;
                grid-template-columns: repeat(5, minmax(280px, 1fr));
                grid-template-rows: auto auto;
                justify-items: stretch;
                align-items: center;
            }
            #boxContainer.raffle-layout-value-10 .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 10px 16px;
                min-width: 0;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            /* Raffle box mặc định (fallback cho value khác) */
            .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 15px 10px;
                position: relative;
                flex: 1 1 0;
                min-width: 0;
            }

            /* Tên người trúng giải (email) - màu tối trên nền trắng, dễ đọc */
            .raffle-name {
                font-size: clamp(11px, 2.5vw, 28px);
                font-weight: 600;
                line-height: 1.2;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
                color: #222;
                text-transform: none;
                letter-spacing: 0.5px;
                padding: 4px 6px;
            }

            /* Giải nhất (1 ô): chữ nhỏ, hiển thị full email không cắt */
            #boxContainer.raffle-layout-value-1 .raffle-name {
                font-size: clamp(11px, 2vw, 18px);
                white-space: nowrap;
                overflow: visible;
                text-overflow: clip;
                max-width: none;
            }

            /* Giải ba (3 ô): chữ nhỏ, hiển thị full email không cắt */
            #boxContainer.raffle-layout-value-3 .raffle-name {
                font-size: clamp(14px, 1.8vw, 16px);
                white-space: nowrap;
                overflow: visible;
                text-overflow: clip;
                max-width: none;
            }

            /* Giải khuyến khích (10 ô): chữ nhỏ, hiển thị full email không cắt */
            #boxContainer.raffle-layout-value-10 .raffle-name {
                font-size: clamp(10px, 1.8vw, 15px);
                padding: 4px 8px;
                white-space: nowrap;
                overflow: visible;
                text-overflow: clip;
                max-width: none;
            }

            /* Chỉ hiển thị email, không hiển thị phần bên dưới ô */
            .raffle-company {
                display: none !important;
            }

            /* Animation khi đang quay */
            .raffle-box.spinning .raffle-name {
                animation: pulse-text 0.12s infinite;
            }

            @keyframes pulse-text {
                0%, 100% {
                    opacity: 1;
                    transform: scale(1);
                }
                50% {
                    opacity: 0.7;
                    transform: scale(1.03);
                }
            }

            /* Khi có kết quả */
            .raffle-box.winner .raffle-name {
                /* Giữ màu tối, không hiệu ứng sáng */
            }

            @keyframes winner-glow-text {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.9; }
            }

            @keyframes fade-in-up {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Ẩn finalResult div vì không cần */
            #finalResult {
                display: none !important;
            }
        </style>
    </head>

    <body>

            @php
                $draw = 1;
                $totalPrizeClass = "text-light";
                $totalPrizes = $luckyDrawRewards->count();
                $totalClients = $luckyDrawClients->count() - $luckyDrawWinners->count();

                if ($totalPrizes < 0) {
                    $totalPrizeClass = "text-danger";
                }

                $drawTxt = "Raffle Draw";
                $luckyDrawReward = $luckyDrawRewards->first();
            @endphp

            <div class="row align-items-center" style="height: 100vh; background: url('{{ isset($luckyDrawReward->img_link) ? $luckyDrawReward->img_link : '' }}') no-repeat center center; background-size: cover;">
                @include('backend.lucky-draw.raffle._firework')

                @if(!isset($luckyDrawReward) || !$luckyDrawReward->order_name)
                <div class="reward-name">
                    <div style="display: flex; align-items: center; justify-content: center; height: 60vh;">
                        <img src="https://checkin.giltech.com.vn/storage/medias/624/CodeLuckydraw_nobox_nhat.jpg" alt="End of prizes" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                @endif

                @if($luckyDrawReward)
                    @php
                        $winnerCount = $luckyDrawClients->where('reward_id', $luckyDrawReward->id)->count();
                    @endphp

                <div class="col-lg-10 mx-auto text-center">
                    <div class="raffle-container">
                        <!-- Tên giải thưởng viết hoa -->
                        <div class="prize-name">
                            {{ strtoupper($luckyDrawReward->name ?? '') }}
                        </div>

                        <div id="data-clients" class=""
                            data-clients="{{ json_encode($luckyDrawClients) }}"
                            data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                            data-reward_id="{{ $luckyDrawReward->id }}"
                            data-time="{{ $luckyDrawReward->time }}"
                            data-value="{{ $luckyDrawReward->value }}"
                            data-winner_count="{{ $winnerCount }}"
                        ></div>

                        <div id="finalResult" style="display: none;"></div>

                        <div class="box-container raffle-layout-value-{{ $luckyDrawReward->value }}" id="boxContainer"></div>
                    </div>

                    <audio id="raffleSound" src="{{ asset('assets/sounds/scan/1.mp3') }}" loop></audio>
                    <audio id="victorySound" src="{{ asset('assets/sounds/scan/victory.mp3') }}" loop></audio>

                    <div id="btn-block" class="" style="opacity: 0">
                        <button id="startButton" class="btn btn-warning text-white rounded shadow" onclick="startRaffle()">
                            Bắt đầu
                            <i class="bx bx-gift bx-tada"></i>
                        </button>
                        <div id="btn-save-block" style="display: none;">
                            <button id="cancelButton" class="btn btn-danger btn-sm text-white rounded shadow" onclick="hideSaveBtnBlock()">
                                Huỷ
                                <i class="bx bx-x-circle"></i>
                            </button>
                            <button id="saveButton" class="btn btn-secondary btn-sm text-white rounded shadow" onclick="saveRaffleResult()">
                                Lưu & Tiếp tục
                                <i class="bx bx-save"></i>
                            </button>
                        </div>
                    </div>

                </div>

                @else
                    <div id="end-message" class="text-white text-center">

                    </div>
                @endif
            </div>

        </div>
    </body>

    <script src="{{ asset('js/lucky-draw/customs/shoppe-food.js') }}"></script>
    <script>
        document.addEventListener('keydown', function(event) {
            // Chỉ cho phép Space để quay khi chưa có kết quả (chưa hiển thị btn-save-block)
            if (event.keyCode === 32) {
                const saveBtnBlock = document.getElementById('btn-save-block');
                const startButton = document.getElementById('startButton');

                // Khóa: không cho Space khi đã có kết quả hoặc đang quay lại một ô
                if (saveBtnBlock && saveBtnBlock.style.display === 'block') {
                    event.preventDefault();
                    return; // Không làm gì khi đã có kết quả
                }

                // Chỉ cho phép khi chưa có kết quả và nút Start chưa bị disable
                if (startButton && !startButton.disabled && startButton.style.display !== 'none') {
                    event.preventDefault();
                    startButton.click();
                    startButton.disabled = true;
                }
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 13 && document.getElementById('btn-save-block').style.display === 'block') {
                event.preventDefault();
                document.getElementById('saveButton').click();
            }
        });
    </script>
</html>
