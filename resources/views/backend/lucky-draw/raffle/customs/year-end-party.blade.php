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
            Lucky Draw - Raffle | {{ config('app.name', 'Delfi Technologies') }}
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

        <link rel="preload" href="https://ck.delfi.vn/file/access/95" as="image">
        <link rel="preload" href="https://ck.delfi.vn/file/access/94" as="image">

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
                margin-top: -41vh;
            }

            /* Tên giải thưởng - uppercase */
            .prize-name {
                font-size: 1.5em;
                font-weight: 600;
                color: #ffffff;
                text-transform: uppercase;
                text-align: center;
                letter-spacing: 2px;
                margin-bottom: 50px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                max-width: 90%;
            }

            /* Container cho raffle box */
            #boxContainer {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                min-height: auto !important;
                gap: 20px;
            }

            /* Raffle box - không có background, chỉ chứa text */
            .raffle-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px 50px;
                position: relative;
                min-width: 400px;
            }

            /* Tên người trúng giải / Số NO - cực lớn, nổi bật với gradient vàng/đồng */
            .raffle-name {
                font-size: 120px;
                font-weight: bold;
                line-height: 1.1;
                text-align: center;
                background: linear-gradient(180deg, 
                    #f5d998 0%, 
                    #d4a853 25%, 
                    #f5d998 50%, 
                    #c9963c 75%, 
                    #f5d998 100%
                );
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-shadow: 0 0 12px rgba(255, 255, 255, 0.5);
                filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.8)) drop-shadow(0 0 40px rgba(245, 217, 152, 0.9));
                text-transform: uppercase;
                letter-spacing: 3px;
                padding: 10px 20px;
                max-width: 90vw;
                word-wrap: break-word;
            }

            /* Tên người trúng / công ty - rất to, sáng hơn nữa */
            .raffle-company {
                font-size: 40px;
                font-weight: 500;
                color: rgba(255, 255, 255, 0.98);
                text-align: center;
                letter-spacing: 2px;
                padding: 12px 30px;
                position: relative;
                text-transform: uppercase;
                text-shadow: 0 0 10px rgba(255, 255, 255, 0.4), 0 0 25px rgba(245, 217, 152, 0.7);
            }

            /* Đường kẻ trang trí trên/dưới company */
            .raffle-company::before,
            .raffle-company::after {
                content: '';
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                width: 60%;
                height: 1px;
                background: linear-gradient(90deg, 
                    transparent 0%, 
                    rgba(212, 168, 83, 0.6) 20%, 
                    rgba(245, 217, 152, 0.8) 50%, 
                    rgba(212, 168, 83, 0.6) 80%, 
                    transparent 100%
                );
            }

            .raffle-company::before {
                top: 0;
            }

            .raffle-company::after {
                bottom: 0;
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
                animation: winner-glow-text 2s ease-in-out infinite;
            }

            .raffle-box.winner .raffle-company {
                animation: fade-in-up 0.8s ease-out forwards;
            }

            @keyframes winner-glow-text {
                0%, 100% { 
                    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5)) drop-shadow(0 0 30px rgba(245, 217, 152, 0.4));
                }
                50% { 
                    filter: drop-shadow(0 6px 15px rgba(0, 0, 0, 0.6)) drop-shadow(0 0 50px rgba(245, 217, 152, 0.7));
                }
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
                        <img src="https://checkin.delfi.vn/storage/medias/540/hinh_travellive_compressed.jpg" alt="End of prizes" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                @endif

                @if($luckyDrawReward)
                    @php
                        $winnerCount = $luckyDrawClients->where('reward_id', $luckyDrawReward->id)->count();
                    @endphp

                <div class="col-lg-10 mx-auto text-center">
                    <div class="raffle-container">
                        {{-- Ẩn tên giải thưởng, không cần hiển thị --}}

                        <div id="data-clients" class=""
                            data-clients="{{ json_encode($luckyDrawClients) }}"
                            data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                            data-reward_id="{{ $luckyDrawReward->id }}"
                            data-assignee_id="{{ $luckyDrawReward->assignee_id }}"
                            data-time="{{ $luckyDrawReward->time }}"
                            data-value="{{ $luckyDrawReward->value }}"
                            data-winner_count="{{ $winnerCount }}"
                        ></div>

                        <div id="finalResult" style="display: none;"></div>

                        <div class="box-container" id="boxContainer"></div>
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

    <script src="{{ asset('js/lucky-draw/customs/year-end-party.js') }}"></script>
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 32) {
                if (event.repeat) return;
                event.preventDefault();
                // Nếu đang hiện block Lưu/Huỷ thì Space không được start lại lượt quay
                const saveBlock = document.getElementById('btn-save-block');
                if (saveBlock && saveBlock.style.display === 'block') {
                    // Logic Space để "Huỷ" đã xử lý trong year-end-party.js
                    return;
                }

                const startBtn = document.getElementById('startButton');
                if (!startBtn || startBtn.disabled) return;
                startBtn.click();
                startBtn.disabled = true;
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
                if (event.repeat) return;
                event.preventDefault();
                stopRaffleEarly();
            }
        });
    </script>
</html>
