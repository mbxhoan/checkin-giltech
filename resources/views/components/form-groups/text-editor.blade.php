<div id="editor-data" class="{{ $class ?? null }}">
    <textarea
        id="{{ $id }}"
        name="{{ $fieldName }}"
        data-url="{{ $dataUrl }}"
        data-token="{{ $dataToken }}"
    >
        {{ $content }}
    </textarea>
</div>
