<h5 class="tutor-text bg-light text-white p-2 rounded shadow-sm">
    <a class="text-decoration-none text-dark collapsed"
        data-bs-toggle="collapse"
        href="#collapseDatas"
        role="button"
        aria-expanded="true"
        aria-controls="collapseDatas"
    >
        {{ $menuCount }}. {{ $features["e-{$menuCount}"]['name'] ?? "UNKNOWN" }}
    </a>
    @include('components._btn-tutor', [
        'key' => 'event-detail-4'
    ])
</h5>
@include('admin.events._datas', [
    'event'         => $model,
])
