<div class="">
    @php
        $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
    @endphp

    <div id="progress-bar"
        class="progress"
        data-url="{{ $dataUrl ?? null }}"
        data-time="{{ $dataTime ?? 5 }}"
        data-ele="{{ $dataEle ?? null }}"
        style="height: 20px; width: {{ $width ?? "" }}px;"
    >
        <div class="progress-bar {{ $percent == 100 ? 'bg-success' : 'progress-bar-striped progress-bar-animated ' }}"
            role="progressbar"
            aria-valuenow="{{ $percent }}"
            aria-valuemin="0"
            aria-valuemax="100"
            style="width: {{ $percent }}%;"
        >
            {{ $percent }}%
            ({{ $completed }}/{{ $total }})
        </div>
    </div>
</div>
