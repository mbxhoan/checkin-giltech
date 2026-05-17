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

            /* Container cho raffle box */
            #boxContainer {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: auto !important;
                margin-top: -54vh;
            }

            /* Raffle box với gradient cam như hình mẫu */
            .raffle-box {
                min-width: 600px;
                max-width: 900px;
                height: 100px;
                padding: 15px 40px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                /* Gradient cam như hình mẫu */
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 50%, #ff6b35 100%);
                border-radius: 50px;
                box-shadow:
                    0 8px 30px rgba(247, 147, 30, 0.5),
                    0 4px 15px rgba(255, 107, 53, 0.3),
                    inset 0 2px 0 rgba(255, 255, 255, 0.3),
                    inset 0 -2px 0 rgba(0, 0, 0, 0.1);
                border: 3px solid rgba(255, 255, 255, 0.4);
                position: relative;
                overflow: hidden;
            }

            /* Hiệu ứng sáng bóng trên box */
            .raffle-box::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                transition: left 0.5s ease;
            }

            .raffle-box:hover::before {
                left: 100%;
            }

            /* Text hiển thị kết quả */
            .raffle-result-text {
                font-size: 1em;
                font-weight: bold;
                color: #ffffff;
                text-shadow:
                    1px 1px 2px rgba(0, 0, 0, 0.2);
                text-align: center;
                white-space: nowrap;
                letter-spacing: 1px;
            }

            /* Text hiển thị chức vụ */
            .raffle-result-chucvu {
                font-size: 0.7em;
                font-weight: 500;
                color: #ffffff;
                text-shadow:
                    1px 1px 2px rgba(0, 0, 0, 0.2);
                text-align: center;
                white-space: nowrap;
                letter-spacing: 0.5px;
                margin-top: -20px;
                opacity: 0.95;
            }

            /* Animation khi đang quay */
            .raffle-box.spinning {
                animation: pulse 0.3s infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    box-shadow:
                        0 8px 30px rgba(247, 147, 30, 0.5),
                        0 4px 15px rgba(255, 107, 53, 0.3),
                        inset 0 2px 0 rgba(255, 255, 255, 0.3),
                        inset 0 -2px 0 rgba(0, 0, 0, 0.1);
                }
                50% {
                    box-shadow:
                        0 8px 40px rgba(247, 147, 30, 0.8),
                        0 4px 20px rgba(255, 107, 53, 0.6),
                        inset 0 2px 0 rgba(255, 255, 255, 0.4),
                        inset 0 -2px 0 rgba(0, 0, 0, 0.1);
                }
            }

            /* Khi có kết quả */
            .raffle-box.winner {
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 30%, #ffa500 50%, #f7931e 70%, #ff6b35 100%);
                animation: winner-glow 1.5s ease-in-out infinite;
            }

            @keyframes winner-glow {
                0%, 100% {
                    box-shadow:
                        0 8px 30px rgba(247, 147, 30, 0.6),
                        0 4px 15px rgba(255, 107, 53, 0.4),
                        0 0 30px rgba(255, 165, 0, 0.4);
                }
                50% {
                    box-shadow:
                        0 8px 50px rgba(247, 147, 30, 0.9),
                        0 4px 25px rgba(255, 107, 53, 0.7),
                        0 0 50px rgba(255, 165, 0, 0.6);
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
                // $totalPrizes = $luckyDrawRewards->count() - $luckyDrawWinners->count();
                $totalPrizes = $luckyDrawRewards->count();
                $totalClients = $luckyDrawClients->count() - $luckyDrawWinners->count();

                if ($totalPrizes < 0) {
                    $totalPrizeClass = "text-danger";
                }

                $drawTxt = "Raffle Draw";
                $luckyDrawReward = $luckyDrawRewards->first();
            @endphp

            <div class="row align-items-center"  style="height: 100vh; background: url('{{ isset($luckyDrawReward->img_link) ? $luckyDrawReward->img_link : '' }}') no-repeat center center; background-size: cover;">
                @include('backend.lucky-draw.raffle._firework')
                <div class="reward-name">
                    @if(isset($luckyDrawReward) && $luckyDrawReward->order_name)
                    @else
                    <div style="display: flex; align-items: center; justify-content: center; height: 60vh;">
                        <img src="https://checkin.giltech.com.vn/storage/medias/523/ketthuc.jpg" alt="End of prizes" style="max-width: 100%; height: auto;">
                    </div>
                    @endif
                    @if($luckyDrawReward)
                        @php
                            $winnerCount = $luckyDrawClients->where('reward_id', $luckyDrawReward->id)->count();
                        @endphp

                    @endif
                </div>
                @if($luckyDrawReward)

                <div class="col-lg-8 mx-auto text-center">
                    <div id="data-clients" class=""
                        data-clients="{{ json_encode($luckyDrawClients) }}"
                        data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                        data-reward_id="{{ $luckyDrawReward->id }}"
                        data-time="{{ $luckyDrawReward->time }}"
                        data-value="{{ $luckyDrawReward->value }}"
                        data-winner_count="{{ $winnerCount }}"
                    ></div>

                    <div id="finalResult" style="
                                display: none;
                                margin-top: 20px;
                                font-size: 1.2em;
                                font-weight: bold;
                                border-radius: 10px;
                                border: 1px solid #f0a608;
                                background-color: #f0a608;
                                color: #ffffff;
                                padding: 10px 0;
                            "
                        >
                    </div>

                    <div class="box-container" id="boxContainer"></div>
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

    <script src="{{ asset('js/lucky-draw/customs/tba-lucky-draw.js') }}"></script>
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 32) {
                event.preventDefault();
                document.getElementById('startButton').click();
                document.getElementById('startButton').disabled = true;  // Simulate button click
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 13 && document.getElementById('btn-save-block').style.display === 'block') {
                event.preventDefault();
                document.getElementById('saveButton').click();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.keyCode === 27) { // ESC key
                event.preventDefault();
                stopRaffleEarly();
            }
        });


    </script>
</html>
