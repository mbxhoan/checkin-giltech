@if ($route)
    <a target="{{ $newTab ? "_blank" : null }}" href="{{ $route }}" class="{{ $class ?? "btn" }}">
        {{ $text ?? null }}
        <x-icon name="{{ $iconClass }}"/>
    </a>
@endif
