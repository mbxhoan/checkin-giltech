@if ($route)
    <a href="{{ $route }}" class="{{ $class ?? "btn" }}">
        {{ $text ?? null }}
        <x-icon name="edit" />
    </a>
@endif
