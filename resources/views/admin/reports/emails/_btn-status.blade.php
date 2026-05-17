<button class="btn btn-xs p-1 text-primary btn-change-status btns-{{ $email->id }} {{ $email->status == 'WAITING' ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ 'SENT' }}"
    title="Gửi"
    {{-- data-url="{{ route('admin.emails.change-status', $email) }}" --}}
    data-id={{ $email->id }}
    data-target_status={{ 'SENT' }}
>
    <x-icon name="forward"/>
</button>
<button class="btn btn-xs p-1 text-secondary btn-change-status btns-{{ $email->id }}
    {{ in_array($email->status, [
        'SENT',
        'NEW',
    ])  ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ 'WAITING' }}"
    title="Gửi lại"
    {{-- data-url="{{ route('admin.emails.change-status', $email) }}" --}}
    data-id={{ $email->id }}
    data-target_status={{ 'WAITING' }}
>
    <x-icon name="play"/>
</button>
<button class="btn btn-xs p-1 text-danger btn-change-status btns-{{ $email->id }} {{ $email->status == 'WAITING' ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ 'NEW' }}"
    title="Dừng"
    {{-- data-url="{{ route('admin.emails.change-status', $email) }}" --}}
    data-id={{ $email->id }}
    data-target_status={{ 'NEW' }}
>
    <x-icon name="pause"/>
</button>
