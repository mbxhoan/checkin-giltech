<button class="btn btn-xs p-1 text-primary btn-change-status btns-{{ $email->id }} {{ $email->status == $email::STATUS_WAITING ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ $email::STATUS_SENT }}"
    title="Gửi"
    data-url="{{ route('admin.emails.change-status', $email) }}"
    data-id={{ $email->id }}
    data-target_status={{ $email::STATUS_SENT }}
>
    <x-icon name="forward"/>
</button>
<button class="btn btn-xs p-1 text-secondary btn-change-status btns-{{ $email->id }}
    {{ in_array($email->status, [
        $email::STATUS_SENT,
        $email::STATUS_NEW,
    ])  ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ $email::STATUS_WAITING }}"
    title="Gửi lại"
    data-url="{{ route('admin.emails.change-status', $email) }}"
    data-id={{ $email->id }}
    data-target_status={{ $email::STATUS_WAITING }}
>
    <x-icon name="play"/>
</button>
<button class="btn btn-xs p-1 text-danger btn-change-status btns-{{ $email->id }} {{ $email->status == $email::STATUS_WAITING ? '' : 'd-none' }}"
    id="btn-change-status-to-{{ $email::STATUS_NEW }}"
    title="Dừng"
    data-url="{{ route('admin.emails.change-status', $email) }}"
    data-id={{ $email->id }}
    data-target_status={{ $email::STATUS_NEW }}
>
    <x-icon name="pause"/>
</button>
