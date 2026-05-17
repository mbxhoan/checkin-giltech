<h5 class="tutor-text bg-light text-white p-2 rounded shadow-sm">
    <a class="text-decoration-none text-dark"
        data-bs-toggle="collapse"
        href="#collapseSettings"
        role="button"
        aria-expanded="true"
        aria-controls="collapseSettings"
    >
        {{ $menuCount }}. {{ $features["e-{$menuCount}"]['name'] ?? "UNKNOWN" }}
    </a>
    @include('components._btn-tutor', [
        'key' => 'event-detail-3'
    ])
    @sys_admin
        <a id="btn-sync-settings" href="" class="text-sm sync-config btn-get" title="Đồng bộ cài đặt"
            data-url="{{ route('admin.event_settings.sync-settings', [
                'event' => $model
            ]) }}"
        >
            <x-icon name="rotate"/>
        </a>
    @endsys_admin
</h5>
<div id="settings">
    @if (($settings && $settings->count()))
        @include('admin.events._settings', [
            'event'     => $model,
            'setting'   => $setting,
            'settings'  => $settings,
        ])
    @endif
</div>
