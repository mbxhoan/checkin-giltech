<div {{ $attributes->class(['card surface-card border-0 shadow-sm']) }}>
    @if (isset($title))
        <div class="card-header border-0 bg-transparent">
            {{ $title }}
        </div>
    @endif

    @if (isset($image))
        {{ $image }}
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="card-footer border-0 bg-transparent">
            {{ $footer }}
        </div>
    @endif
</div>
