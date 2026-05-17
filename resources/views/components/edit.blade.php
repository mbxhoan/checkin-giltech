<span
    class="text"
    id="{{ $dataId }}"
>
    {{ $text }}
</span>
<a href=""
    class="text-primary {{ $class ?? null }}"
    data-id="{{ $dataId }}"
    data-field="{{ $fieldName }}"
    data-url="{{ $dataUrl }}"
    data-prompt="{{ $dataPrompt ?? null }}"
>
    <x-icon name="edit" />
</a>
