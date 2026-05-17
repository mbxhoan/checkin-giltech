<div class="tutor-text bg-light text-white px-2 pt-2 rounded shadow-sm">
    <div class="row">
        <div class="col-md-10">
            <h5 class="">
                <a class="text-decoration-none text-dark collapsed"
                    data-bs-toggle="collapse"
                    href="#collapseMedias"
                    role="button"
                    aria-expanded="true"
                    aria-controls="collapseMedias"
                >
                    {{ $blockCount }}. {{ $features["e-{$menuCount}"]['name'] ?? "UNKNOWN" }}
                </a>
                @include('components._btn-tutor', [
                    'key' => 'event-detail-5'
                ])
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
@include('admin.events._medias', [
    'event'         => $event,
    'eventFiles'    => $eventFiles,
])
