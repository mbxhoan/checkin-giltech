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

    * {
        font-family: 'Be Vietnam Pro', sans-serif !important;
    }

    /* Golden Box Design - Matching image: racetrack shape, light golden/beige fill, 3D border */
    .raffle-box {
        width: 100%;
        height: 5.5vh;
        min-height: 2.5rem;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.5vh 1.2vh;
        background: radial-gradient(ellipse at center,
            #f0e6c8 0%,
            #e5d9b0 40%,
            #d9cca0 70%,
            #d4c494 100%
        );
        border: 0.2vh solid #c9a227;
        border-radius: 2vh;
        box-shadow:
            0 0.3vh 0.5vh rgba(201, 162, 39, 0.4),
            inset 0 0.2vh 0.4vh rgba(255, 255, 255, 0.6),
            inset 0 -0.2vh 0.4vh rgba(0, 0, 0, 0.08);
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
        height: 50%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.35) 0%, transparent 100%);
        border-radius: 2vh 2vh 0 0;
        pointer-events: none;
    }

    .raffle-box.winner {
        background: radial-gradient(ellipse at center,
            #f5ecd0 0%,
            #ebe0b8 40%,
            #e0d4a8 70%,
            #d9cca0 100%
        );
        border-color: #b8860b;
        box-shadow:
            0 0 2vh rgba(255, 215, 0, 0.4),
            0 0.3vh 0.5vh rgba(201, 162, 39, 0.4),
            inset 0 0.2vh 0.4vh rgba(255, 255, 255, 0.7),
            inset 0 -0.2vh 0.4vh rgba(0, 0, 0, 0.08);
    }

    .raffle-box.shuffling {
        animation: goldPulse 0.15s infinite;
    }

    @keyframes goldPulse {
        0%, 100% {
            box-shadow: 0 0.3vh 0.5vh rgba(201, 162, 39, 0.4), inset 0 0.2vh 0.4vh rgba(255, 255, 255, 0.6);
        }
        50% {
            box-shadow: 0 0.4vh 1vh rgba(255, 215, 0, 0.4), inset 0 0.2vh 0.4vh rgba(255, 255, 255, 0.8);
        }
    }

    /* Clickable state - for re-spin */
    .raffle-box.clickable {
        cursor: pointer;
        position: relative;
    }

    .raffle-box.clickable:hover {
        transform: scale(1.02);
        box-shadow:
            0 0 2.5vh rgba(255, 0, 0, 0.35),
            0 0.3vh 0.5vh rgba(201, 162, 39, 0.4),
            inset 0 0.2vh 0.4vh rgba(255, 255, 255, 0.7),
            inset 0 -0.2vh 0.4vh rgba(0, 0, 0, 0.08);
        border-color: #ff4444;
    }

    .raffle-box.clickable:hover::after {
        content: 'Click để quay lại';
        position: absolute;
        bottom: -2.2vh;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1vh;
        color: #ff4444;
        white-space: nowrap;
        font-weight: 600;
        text-shadow: 0 0 0.5vh rgba(0,0,0,0.8);
    }

    .raffle-box.clickable:active {
        transform: scale(0.98);
    }

    /* Shuffling name - only name visible during spin */
    .shuffle-name {
        font-size: 1.5vh;
        font-weight: 700;
        color: #1a1a1a;
        text-align: center;
        text-transform: uppercase;
        text-shadow: 0 0.1vh 0.2vh rgba(255, 255, 255, 0.5);
        white-space: nowrap;
        width: 100%;
    }

    /* Winner: manv (top), name (middle), company (bottom) */
    .winner-manv {
        font-size: 1.2vh;
        font-weight: 700;
        color: #4a3500;
        text-align: center;
        margin-bottom: 0.15vh;
        text-transform: uppercase;
        text-shadow: 0 0.1vh 0.2vh rgba(255, 255, 255, 0.5);
        white-space: nowrap;
        width: 100%;
        line-height: 1.2;
    }

    .winner-name {
        font-size: 1.05vh;
        font-weight: 700;
        color: #1a1a1a;
        text-align: center;
        margin-bottom: 0.15vh;
        text-transform: uppercase;
        text-shadow: 0 0.1vh 0.2vh rgba(255, 255, 255, 0.5);
        white-space: nowrap;
        width: 100%;
        line-height: 1.2;
    }

    .winner-code {
        font-size: 1.05vh;
        font-weight: 600;
        color: #4a3500;
        text-align: center;
        width: 100%;
        opacity: 0.9;
        line-height: 1.2;
    }

    #winnerList {
        display: none;
    }

    /* Grid columns from --grid-cols (2-10), vh-based spacing */
    #boxContainer {
        display: grid;
        grid-template-columns: repeat(var(--grid-cols, 10), 1fr);
        gap: 1vh;
        padding: 0;
        margin: 0;
        width: 100%;
        box-sizing: border-box;
    }

    /* Full width, equal side margins (1vh each), centered, positioned lower */
    .winners-panel {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: calc(100% - 2vh);
        box-sizing: border-box;
        padding: 0 1vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        max-height: 65vh;
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

            <div class="row align-items-center position-relative" style="height: 100vh; background: url('{{ isset($luckyDrawReward->img_link) ? $luckyDrawReward->img_link : 'https://checkin.delfi.vn/storage/medias/629/BG-trống.png' }}') no-repeat center center; background-size: cover;">
                @include('backend.lucky-draw.raffle._firework')
                <div class="reward-name">

                    @if(isset($luckyDrawReward) && $luckyDrawReward->order_name)

                    @else
                        <span></span>
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

                <div id="data-clients" class=""
                    data-clients="{{ json_encode($luckyDrawClients) }}"
                    data-url="{{ route('admin.lucky_draws.update-raffle') }}"
                    data-reward_id="{{ $luckyDrawReward->id }}"
                    data-assignee_id="{{ $luckyDrawReward->assignee_id }}"
                    data-assignee_ids="{{ $luckyDrawReward->assignees?->pluck('id')->implode(',') }}"
                    data-time="{{ $luckyDrawReward->time }}"
                    data-value="{{ $luckyDrawReward->value }}"
                ></div>

                <div id="finalResult" style="display: none;"></div>

                <!-- Winners Panel - Centered -->
                <div class="winners-panel">
                    <div class="box-container" id="boxContainer" style="--grid-cols: {{ min(10, max(2, (int)($luckyDrawReward->value ?? 10))) }};"></div>
                </div>

                <audio id="raffleSound" src="{{ asset('assets/sounds/scan/1.mp3') }}" loop></audio>
                <audio id="victorySound" src="{{ asset('assets/sounds/scan/victory.mp3') }}" loop></audio>

                <div id="btn-block" class="" style="opacity: 0; position: fixed; bottom: 3vh; left: 50%; transform: translateX(-50%); z-index: 100;">
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

                @else
                    <div id="end-message" class="text-white text-center">

                    </div>
                @endif
            </div>

        </div>
    </body>

    <script src="{{ asset('js/lucky-draw/customs/flix.js') }}"></script>
</html>
