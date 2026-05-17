@if (isset($edit) && $edit)
    <a id="{{ $btnId ?? 'btn-submit' }}" class="{{ $btnClass ?? 'btn btn-primary' }}"
        href="{{ route('admin.landing_pages.edit', [
            'event'         => $model->event,
            'landing_page'  => $model,
            'lang'          => $language ? $language->code : request()->lang,
            'is_success'    => true,
        ]) }}"
    >
        {!! $btnText !!}
        <i class='bx bx-right-arrow-alt' ></i>
    </a>
@else
    <button id="{{ $btnId ?? 'btn-submit' }}" type="button" class="{{ $btnClass ?? 'btn btn-primary' }}">
        {!! $btnText !!}
        <i class='bx bx-right-arrow-alt' ></i>
    </button>
@endif
@if (isset($edit) && $edit)
    @include('admin.landing_pages._edit', [
        'id'            => $id,
        'text'          => $btnText,
        'language'      => $language,
        'eventId'       => $eventId,
        'model'         => $model,
    ])
@endif
