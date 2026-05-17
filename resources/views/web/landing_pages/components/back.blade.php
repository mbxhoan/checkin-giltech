<div class="{{ $divClass ?? null }} text-center">
    <a id="{{ $btnId ?? 'btn-back' }}" class="{{ $btnClass ?? 'btn btn-primary' }}"
        href="{{ $routeBack ?? "#" }}"
    >
        <x-icon name="arrow-left" />
        {!! $btnText !!}
    </a>

    @if (isset($edit) && $edit)
        @include('admin.landing_pages._edit', [
            'id'            => $id,
            'text'          => $btnText,
            'language'      => $language,
            'eventId'       => $eventId,
            'model'         => $model,
        ])
    @endif
</div>
