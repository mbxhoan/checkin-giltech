<div class="tutor-text bg-light text-white px-2 pt-2 rounded shadow-sm">
    <div class="row">
        <div class="col-md-10">
            <h5 class="">
                <a class="text-decoration-none text-dark"
                    data-bs-toggle="collapse"
                    href="#collapseLabels"
                    aria-controls="collapseLabels"
                >
                    {{ $blockCount }}. {{ $features["e-10"]['name'] ?? "UNKNOWN" }}
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
<div id="labels">
    @include('admin.labels._list', [
        'event'         => $event,
        'labels'        => $labels,
    ])
</div>
