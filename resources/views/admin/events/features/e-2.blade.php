@if (($customFieldTemplates && $customFieldTemplates->count()))
    <h5 class="tutor-text bg-light text-white p-2 rounded shadow-sm">
        <a class="text-decoration-none text-dark"
            data-bs-toggle="collapse"
            href="#collapseCustomFieldTemplates"
        >
            {{ $menuCount }}. {{ $features["e-{$menuCount}"]['name'] ?? "UNKNOWN" }}
        </a>
        @include('components._btn-tutor', [
            'key' => 'event-detail-2'
        ])
    </h5>
    @include('admin.events._custom-field-templates', [
        'event'                 => $model,
        'customFieldTemplates'  => $customFieldTemplates,
    ])
@endif
