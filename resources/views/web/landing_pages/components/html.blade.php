@if (!empty($text))
    <div class="{{ $divClass }} text-center m-2">
        <span id="{{ $id ?? null }}-text">
            {!! $text !!}
        </span>
        @if (isset($edit) && $edit)
            @include('admin.landing_pages._edit-html', [
                'id'            => $id,
                'text'          => $text,
                'content'       => $content,
                'language'      => $language,
                'eventId'       => $eventId,
                'model'         => $model,
            ])
        @endif
    </div>
@endif
