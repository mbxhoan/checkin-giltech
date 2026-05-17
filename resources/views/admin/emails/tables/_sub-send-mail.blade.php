<table class="table table-striped text-xs">
    <thead>
        <tr>
            <th scope="col">Email</th>
            <th scope="col">Họ tên</th>
            <th scope="col">Thời gian xử lý</th>
            <th scope="col">Log</th>
            <th scope="col">Trạng thái</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emails as $email)
            <tr>
                <td>{{ $email->to_email }}</td>
                <td>
                    {{ $email->to_name }}
                    <a href="" data-bs-toggle="modal" data-bs-target="#{{ $email->id }}Modal">
                        <x-icon name="circle-info" />
                    </a>
                    @include('admin.emails._modal-info', [
                        'modalId' => "{$email->id}Modal",
                        'data' => $email->param,
                    ])
                </td>
                <td id="email-sent_at-{{ $email->id }}">
                    {{ $email->sent_at ? humanize_date($email->sent_at, 'H:i:s d-m-Y') : null }}
                </td>
                <td>{{ $email->error_log }}</td>
                <td id="email-status-{{ $email->id }}">
                    @include('admin.emails._status', [
                        'email' => $email,
                    ])
                </td>
                <td id="btns-status-{{ $email->id }}">
                    @include('admin.emails._btn-status', [
                        'email' => $email,
                    ])
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
