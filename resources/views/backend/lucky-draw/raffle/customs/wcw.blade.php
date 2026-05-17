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

        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Pinyon+Script&family=DM+Serif+Display&display=swap" rel="stylesheet">
        
        <style>
            html, body {
                height: 100%;
                margin: 0;
                overflow: hidden;
                background: #000000;
            }
            
            body {
                background: #000000 !important;
            }

            /* Container chính */
            .raffle-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 113vh;
                padding: 20px;
            }

            /* Tên giải thưởng - uppercase */
            .prize-name {
                font-family: 'Cormorant Garamond', serif !important;
                font-size: 2em;
                font-weight: 600;
                text-transform: uppercase;
                text-align: center;
                letter-spacing: 4px;
                margin-bottom: 60px;
                max-width: 90%;
                background: linear-gradient(180deg, #fff8dc 0%, #ffd700 25%, #daa520 50%, #b8860b 75%, #cd853f 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5)) drop-shadow(0 0 12px rgba(255, 215, 0, 0.4));
            }

            /* Ô số quay - góc trái, nằm trong vùng hình 16:9 (hạ xuống) */
            .raffle-number-banner {
                position: fixed;
                top: 29vh;
                left: 3.5vw;
                z-index: 20;
                padding: 0;
                background: transparent;
                border: none;
                box-shadow: none;
            }

            /* Congrats to + tên người trúng - góc phải, nằm trong vùng hình 16:9 (hạ xuống) */
            .raffle-winner-banner {
                position: fixed;
                top: 27.5vh;
                right: 5vw;
                z-index: 20;
                padding: 0;
                width: 460px; /* Chiều rộng cố định để tên dài/ngắn đều cùng vị trí */
                min-width: 420px;
                background: transparent;
                border: none;
                box-shadow: none;
            }

            .raffle-winner-banner .winner-display {
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0;
                margin-top: 0;
                height: auto;
                min-height: 80px;
                width: 100%;
                text-align: center; /* Luôn căn giữa trong vùng cố định */
            }

            .raffle-winner-banner .congrat-text {
                font-size: 28px;
                margin: 0;
            }

            .raffle-winner-banner .winner-name {
                font-size: 26px;
                letter-spacing: 4px;
                text-align: center; /* Tên luôn căn giữa, không lệch khi tên ngắn */
                width: 100%;
                line-height: 1;
                margin: 0;
                margin-top: -2px; /* Kéo sát "Congrats to" hơn */
            }

            /* Container cho các ô số flip */
            #boxContainer {
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 6px;
                margin-bottom: 0;
            }

            /* Ô số quay nhỏ gọn trong banner góc trái */
            .raffle-number-banner #boxContainer {
                gap: 4px;
            }

            .raffle-number-banner .flip-card {
                width: 40px;
                height: 50px;
                perspective: 600px;
            }

            .raffle-number-banner .flip-number {
                font-size: 28px;
            }

            /* Mỗi ô số flip card (mặc định - dùng khi không trong banner) */
            .flip-card {
                width: 80px;
                height: 95px;
                perspective: 1000px;
                position: relative;
            }

            .flip-card-inner {
                width: 100%;
                height: 100%;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .flip-card-inner::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('/images/lucky-draw/flip-box.png');
                background-size: 100% 100%;
                background-repeat: no-repeat;
                background-position: center;
                z-index: 0;
            }

            /* Số trong ô flip */
            .flip-number {
                font-family: 'DM Serif Display', serif !important;
                font-size: 60px;
                font-weight: 400;
                font-style: normal;
                background: linear-gradient(180deg, #fff8dc 0%, #ffd700 25%, #daa520 50%, #b8860b 75%, #cd853f 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.6)) drop-shadow(0 0 15px rgba(255, 215, 0, 0.5));
                line-height: 1;
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
            }

            /* Animation khi đang quay */
            .flip-card.spinning .flip-number {
                animation: flip-spin 0.08s infinite;
            }

            @keyframes flip-spin {
                0% { 
                    transform: translateY(-5px);
                    opacity: 0.6;
                }
                50% { 
                    transform: translateY(5px);
                    opacity: 1;
                }
                100% { 
                    transform: translateY(-5px);
                    opacity: 0.6;
                }
            }

            /* Khi có kết quả - winner */
            .flip-card.winner .flip-card-inner {
                filter: drop-shadow(0 0 15px rgba(212, 168, 83, 0.5));
            }

            .flip-card.winner .flip-number {
                animation: winner-pulse 2s ease-in-out infinite;
            }

            @keyframes winner-pulse {
                0%, 100% { 
                    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.6)) drop-shadow(0 0 15px rgba(255, 215, 0, 0.5));
                }
                50% { 
                    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.7)) drop-shadow(0 0 30px rgba(255, 215, 0, 0.8)) drop-shadow(0 0 50px rgba(255, 200, 0, 0.5));
                }
            }

            /* Khu vực hiển thị tên người trúng */
            .winner-display {
                text-align: center;
                display: flex;
                flex-direction: row;
                align-items: baseline;
                justify-content: center;
                gap: 20px;
                opacity: 0;
                transition: opacity 0.8s ease-out;
                width: 100%;
                height: 120px;
                margin-top: -20px;
            }

            .winner-display.show {
                opacity: 1;
            }

            /* Text "Congrat to" - font chữ viết tay */
            .congrat-text {
                font-family: 'Pinyon Script', cursive !important;
                font-size: 38px;
                font-weight: 400;
                letter-spacing: 1px;
                font-style: italic;
                background: linear-gradient(180deg, #fff8dc 0%, #ffd700 30%, #daa520 60%, #cd853f 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5)) drop-shadow(0 0 10px rgba(255, 215, 0, 0.4));
            }

            /* Tên người trúng - font serif sang trọng */
            .winner-name {
                font-family: 'Cormorant Garamond', serif !important;
                font-size: 40px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 8px;
                background: linear-gradient(180deg, #ffffff 0%, #fffacd 20%, #ffd700 50%, #ffec8b 80%, #ffffff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.9)) drop-shadow(0 0 20px rgba(255, 255, 150, 0.7)) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
            }

            /* Ẩn finalResult div vì không cần */
            #finalResult {
                display: none !important;
            }

            /* Ẩn raffle-box cũ */
            .raffle-box {
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

            <div class="row align-items-center" style="height: 100vh; background: url('{{ isset($luckyDrawReward->img_link) ? $luckyDrawReward->img_link : '#000000' }}') no-repeat center center; background-size: cover; background-color: #000000;">
                @include('backend.lucky-draw.raffle._firework')
                
                @if(!isset($luckyDrawReward) || !$luckyDrawReward->order_name)
                <div class="reward-name">
                    <div style="display: flex; align-items: center; justify-content: center; height: 60vh;">
                        <img src="https://checkin.delfi.vn/storage/medias/546/Diamond_Voyages_bg.png" alt="End of prizes" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                @endif

                @if($luckyDrawReward)
                    @php
                        $winnerCount = $luckyDrawClients->where('reward_id', $luckyDrawReward->id)->count();
                    @endphp

                <div class="col-lg-12 mx-auto text-center">
                    <div class="raffle-container">
                        <!-- Tên giải thưởng viết hoa -->
                        <!-- <div class="prize-name">
                            {{ strtoupper($luckyDrawReward->name ?? '') }}
                        </div> -->

                        <div id="data-clients" class=""
                            data-clients="{{ json_encode($luckyDrawClients) }}"
                            data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                            data-reward_id="{{ $luckyDrawReward->id }}"
                            data-time="{{ $luckyDrawReward->time }}"
                            data-value="{{ $luckyDrawReward->value }}"
                            data-assignee_id="{{ $luckyDrawReward->assignee_id }}"
                            data-winner_count="{{ $winnerCount }}"
                            data-order="{{ $luckyDrawReward->order }}"
                            data-order_name="{{ $luckyDrawReward->order_name }}"
                            data-lucky_draw_id="{{ $luckyDraw->id }}"
                        ></div>

                        <div id="finalResult" style="display: none;"></div>

                        <!-- Ô khung số quay - góc trái trên -->
                        <div class="raffle-number-banner">
                            <div class="box-container" id="boxContainer">
                                <!-- Flip cards sẽ được tạo bởi JS -->
                            </div>
                        </div>

                        <!-- Khối Congrats to + tên người trúng - góc phải trên -->
                        <div class="raffle-winner-banner">
                            <div class="winner-display" id="winnerDisplay">
                                <div class="congrat-text">Congrats to</div>
                                <div class="winner-name" id="winnerName"></div>
                            </div>
                        </div>
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

    <script src="{{ asset('js/lucky-draw/customs/wcw.js') }}"></script>
    <script>
        // Space key đã được xử lý trong wcw.js

        // Enter key: Lưu kết quả
        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                // Kiểm tra nếu đang xử lý thì bỏ qua
                if (isProcessing) {
                    console.log('Đang xử lý, vui lòng chờ...');
                    return;
                }
                // Chỉ lưu khi save block đang hiển thị và nút save không disabled
                const saveBtnBlock = document.getElementById('btn-save-block');
                const saveButton = document.getElementById('saveButton');
                if (saveBtnBlock.style.display === 'block' && !saveButton.disabled) {
                    saveRaffleResult();
                }
            }
        });

        // ESC key: Dừng quay sớm
        document.addEventListener('keydown', function (event) {
            if (event.keyCode === 27) {
                event.preventDefault();
                stopRaffleEarly();
            }
        });
    </script>
</html>
