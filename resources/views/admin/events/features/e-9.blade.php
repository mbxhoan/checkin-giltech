<div class="tutor-text bg-light text-white px-2 pt-2 rounded shadow-sm">
    <div class="row">
        <div class="col-md-10">
            <h5 class="">
                <a class="text-decoration-none text-dark"
                    data-bs-toggle="collapse"
                    href="#collapseCards"
                    aria-controls="collapseCards"
                >
                    {{ $blockCount }}. {{ $features["e-9"]['name'] ?? "UNKNOWN" }}
                </a>
            </h5>
        </div>
        <div class="col-md-2 text-end">
            @include('admin.events.features._remove-feature', [
                'model' => $event,
                'value' => "e-{$menuCount}",
            ])
        </div>
    </div>
</div>
<div id="cards">
    @include('admin.cards._list', [
        'event'         => $event,
        'cards'         => $cards,
    ])
</div>
