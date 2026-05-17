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
    /* Be Vietnam Pro Font */
    @font-face {
        font-family: 'Be Vietnam Pro';
        src: url('{{ asset("assets/fonts/BEVIETNAM/BEVIETNAMPRO-REGULAR.TTF") }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'Be Vietnam Pro';
        src: url('{{ asset("assets/fonts/BEVIETNAM/BEVIETNAMPRO-MEDIUM.TTF") }}') format('truetype');
        font-weight: 500;
        font-style: normal;
    }
    @font-face {
        font-family: 'Be Vietnam Pro';
        src: url('{{ asset("assets/fonts/BEVIETNAM/BEVIETNAMPRO-SEMIBOLD.TTF") }}') format('truetype');
        font-weight: 600;
        font-style: normal;
    }
    @font-face {
        font-family: 'Be Vietnam Pro';
        src: url('{{ asset("assets/fonts/BEVIETNAM/BEVIETNAMPRO-BOLD.TTF") }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @font-face {
        font-family: 'Be Vietnam Pro';
        src: url('{{ asset("assets/fonts/BEVIETNAM/BEVIETNAMPRO-EXTRABOLD.TTF") }}') format('truetype');
        font-weight: 800;
        font-style: normal;
    }

    .raffle-box {
        flex: 1;
        min-width: 0;
        height: 85px;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px 5px;
        background: linear-gradient(180deg, rgba(20, 40, 80, 0.95) 0%, rgba(10, 25, 60, 0.98) 100%);
        border: 2px solid #4a90d9;
        border-radius: 8px;
        box-shadow:
            0 0 15px rgba(74, 144, 217, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .raffle-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(74, 144, 217, 0.8), transparent);
    }

    .raffle-box.winner {
        border-color: #5ba3ec;
        box-shadow:
            0 0 20px rgba(91, 163, 236, 0.5),
            0 0 40px rgba(91, 163, 236, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    .raffle-box.shuffling {
        animation: boxPulse 0.3s infinite;
    }

    @keyframes boxPulse {
        0%, 100% { border-color: #4a90d9; }
        50% { border-color: #7ab8ff; }
    }

    /* Shuffling name - only name visible during spin */
    .shuffle-name {
        font-size: 10px;
        font-weight: 700;
        color: #ffffff;
        text-align: center;
        text-transform: uppercase;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        white-space: nowrap;
        width: 100%;
    }

    /* Winner Name - Top */
    .winner-name {
        font-size: 10px;
        font-weight: 700;
        color: #ffffff;
        text-align: center;
        margin-bottom: 3px;
        text-transform: uppercase;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        white-space: nowrap;
        width: 100%;
    }

    /* Winner Workplace - Middle */
    .winner-workplace {
        font-size: 7px;
        font-weight: 500;
        color: #b8d4f0;
        text-align: center;
        margin-bottom: 3px;
        width: 100%;
        opacity: 0.95;
        /* Allow 2 lines for long workplace */
        white-space: normal;
        line-height: 1.3;
        max-height: 2.6em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Winner Company - Bottom */
    .winner-company {
        font-size: 9px;
        font-weight: 600;
        color: #ffd700;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
        width: 100%;
    }

    * {
        font-family: 'Be Vietnam Pro', sans-serif !important;
    }

    #winnerList {
        display: none;
    }

    #boxContainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 10px 5px;
        padding: 0;
        margin: 0;
        width: 100vw;
        max-width: 100vw;
        box-sizing: border-box;
        position: absolute;
        left: 0;
        right: 0;
    }

    /* Prize level specific styles */
    /* Giải nhất - 3 winners (1 row of 3) */
    #boxContainer.prize-first .raffle-box {
        flex: 0 0 auto;
        width: 290px;
        height: 100px;
        padding: 12px 15px;
    }
    #boxContainer.prize-first .winner-name,
    #boxContainer.prize-first .shuffle-name {
        font-size: 15px;
    }
    #boxContainer.prize-first .winner-workplace {
        font-size: 15px;
        max-height: 2.8em;
    }
    #boxContainer.prize-first .winner-company {
        font-size: 12px;
    }

    /* Giải nhì - 6 winners (2 rows: 3 + 3) */
    #boxContainer.prize-second .raffle-box {
        flex: 0 0 calc((100% - 20px) / 3);
        max-width: calc((100% - 20px) / 3);
        height: 95px;
        padding: 10px 12px;
    }
    #boxContainer.prize-second .winner-name,
    #boxContainer.prize-second .shuffle-name {
        font-size: 17px;
    }
    #boxContainer.prize-second .winner-workplace {
        font-size: 16px;
        max-height: 2.6em;
    }
    #boxContainer.prize-second .winner-company {
        font-size: 14px;
    }

    /* Giải ba - 9 winners (2 rows: 5 + 4) */
    #boxContainer.prize-third .raffle-box {
        flex: 0 0 calc((100% - 20px) / 5);
        max-width: calc((100% - 20px) / 5);
        height: 88px;
    }
    #boxContainer.prize-third .winner-name,
    #boxContainer.prize-third .shuffle-name {
        font-size: 15px;
    }
    #boxContainer.prize-third .winner-workplace {
        font-size: 15px;
        max-height: 2.4em;
    }
    #boxContainer.prize-third .winner-company {
        font-size: 12px;
    }

    /* Responsive adjustments */
    @media (min-width: 1800px) {
        .raffle-box {
            height: 90px;
            padding: 10px 8px;
        }
        .shuffle-name { font-size: 11px; }
        .winner-name { font-size: 11px; }
        .winner-workplace { font-size: 9px; }
        .winner-company { font-size: 10px; }

        #boxContainer.prize-first .raffle-box {
            width: 370px;
            height: 110px;
        }
        #boxContainer.prize-second .raffle-box {
            height: 100px;
        }
        #boxContainer.prize-third .raffle-box {
            height: 90px;
        }
    }

    @media (max-width: 1600px) {
        .raffle-box {
            height: 80px;
            padding: 8px 5px;
        }
        #boxContainer { gap: 8px 4px; }
        .shuffle-name { font-size: 9px; }
        .winner-name { font-size: 9px; }
        .winner-workplace { font-size: 7px; }
        .winner-company { font-size: 8px; }

        #boxContainer.prize-first .raffle-box {
            width: 280px;
            height: 95px;
        }
        #boxContainer.prize-second .raffle-box {
            flex: 0 0 calc((100% - 16px) / 3);
            max-width: calc((100% - 16px) / 3);
        }
        #boxContainer.prize-third .raffle-box {
            flex: 0 0 calc((100% - 16px) / 5);
            max-width: calc((100% - 16px) / 5);
        }
    }

    @media (max-width: 1400px) {
        .raffle-box {
            height: 75px;
            padding: 6px 4px;
        }
        #boxContainer { gap: 6px 3px; }
        .shuffle-name { font-size: 8px; }
        .winner-name { font-size: 8px; }
        .winner-workplace { font-size: 6px; }
        .winner-company { font-size: 7px; }

        #boxContainer.prize-first .raffle-box {
            width: 250px;
            height: 85px;
        }
        #boxContainer.prize-second .raffle-box {
            flex: 0 0 calc((100% - 12px) / 3);
            max-width: calc((100% - 12px) / 3);
            height: 85px;
        }
        #boxContainer.prize-third .raffle-box {
            flex: 0 0 calc((100% - 12px) / 5);
            max-width: calc((100% - 12px) / 5);
            height: 80px;
        }
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

            <div class="row align-items-center"  style="height: 100vh; background: url('{{ isset($luckyDrawReward->img_link) ? $luckyDrawReward->img_link : 'https://ck.giltech.com.vn/file/access/151' }}') no-repeat center center; background-size: cover;">
                @include('backend.lucky-draw.raffle._firework')
                <div class="reward-name">

                    @if(isset($luckyDrawReward) && $luckyDrawReward->order_name)

                    @else
                        <span>No prize available</span>
                    @endif
                </div>

                {{-- @if(isset($luckyDrawReward->img_link))
                    <div class="prize-image">
                        <img src="{{ $luckyDrawReward->img_link }}" alt="Prize Image" style="max-width: 10%; height: auto;">
                    </div>
                @endif --}}
                {{-- <div class="reward-name">
                    <span>Quay số may mắn</span>
                </div> --}}
                @if($luckyDrawReward)

                <div class="col-lg-8 mx-auto text-center mb-3">
                    <div id="data-clients" class=""
                        data-clients="{{ json_encode($luckyDrawClients) }}"
                        data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                        data-reward_id="{{ $luckyDrawReward->id }}"
                        data-assignee_id="{{ $luckyDrawReward->assignee_id }}"
                        data-time="{{ $luckyDrawReward->time }}"
                        data-value="{{ $luckyDrawReward->value }}"
                    ></div>

                    <div id="finalResult" style="
                                display: none;
                                margin-top: 20px;
                                font-size: 1.1em;
                                font-weight: bold;
                                border-radius: 10px;
                                border: 1px solid #f0a608;
                                background-color: #f0a608;
                                color: #ffffff;
                                padding: 10px 0;
                            "
                        >
                    </div>

                    <div class="box-container" id="boxContainer" style="margin-top: -26vh; padding-left: 40px; padding-right: 40px;"></div>
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

    <script src="{{ asset('js/lucky-draw/customs/hoi-nghi-tong-ket-20.js') }}"></script>
</html>
