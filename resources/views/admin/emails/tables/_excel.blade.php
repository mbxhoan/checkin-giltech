<table>
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Message ID</th>
            <th scope="col">Template ID</th>
            <th scope="col">Subject</th>
            <th scope="col">Sender Name</th>
            <th scope="col">Sender Email</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Status</th>
            <th scope="col">Timestamp</th>
            <th scope="col">Queued at</th>
        </tr>
    </thead>
    <tbody>
        @foreach($emails as $email)
            <tr>
                <td>{{ $email->id }}</td>
                <td>{{ $email->message_id }}</td>
                <td>{{ $email->template_id }}</td>
                <td>{{ $email->subject }}</td>
                <td>{{ $email->from_name }}</td>
                <td>{{ $email->from_email }}</td>
                <td>{{ $email->to_name }}</td>
                <td>{{ $email->to_email }}</td>
                <td>{{ $email->status }}</td>
                <td>{{ $email->sent_at }}</td>
                <td>{{ $email->created_at }}</td>
            </tr>
            @if (!empty($email->webhookPostmarks) && $email->webhookPostmarks->count())
                    @foreach ($email->webhookPostmarks as $webhook)
                    <tr>
                        <th scope="row" style="vertical-align: middle;"></th>
                        <td style="vertical-align: middle;">{{ $webhook->message_id }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->recipient->template_id }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->recipient->subject }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->recipient->from_name }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->recipient->from_email }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->recipient->to_name }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->email }}</td>
                        <td style="vertical-align: middle;">{{ $webhook->status }}</td>
                        <td style="vertical-align: middle;">
                            {{ $webhook->convertToLocalTime() }}
                            {{-- {{ \Carbon\Carbon::parse($webhook->record_time)->toDateTimeString() }} --}}
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>
