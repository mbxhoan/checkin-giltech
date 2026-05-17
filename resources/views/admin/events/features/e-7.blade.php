<div class="tutor-text bg-light text-white px-2 pt-2 rounded shadow-sm">
    <div class="row">
        <div class="col-md-10">
            <h5 class="">
                <a class="text-decoration-none text-dark"
                    data-bs-toggle="collapse"
                    href="#collapseLandingPages"
                    aria-controls="collapseLandingPages"
                >
                    {{ $blockCount }}. {{ $features["e-7"]['name'] ?? "UNKNOWN" }}
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
<div id="landing-pages">
    @include('admin.landing_pages._list', [
        'event'         => $event,
        'landingPages'  => $landingPages,
    ])
</div>
