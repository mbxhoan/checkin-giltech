@extends('admin.layouts.templates.page-index', [
    'pageTitle' => 'Senders',
])

@section('title')
    Senders: <span class="text-danger">{{ $total ?? 0 }}</span>
@endsection

@section('buttons')
    <div class="buttons">

    </div>
@endsection

@section('primary-content')
    <div class="table table-responsive">
        <table class="table table-striped table-hover table-sm ">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Domain</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Reply to</th>
                    <th scope="col">Confirmed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($senders as $sender)
                    <tr class="text-sm" data-href="{{ route('admin.email_senders.edit', $sender['ID']) }}">
                        <th scope="row">
                            {{ $sender['ID'] }}
                        </th>
                        <td>
                            {{ $sender['Domain'] }}
                        </td>
                        <td>
                            {{ $sender['Name'] }}
                        </td>
                        <td>
                            {{ $sender['EmailAddress'] }}
                        </td>
                        <td>
                            {{ $sender['ReplyToEmailAddress'] }}
                        </td>
                        <td class="text-center" >
                            @if ($sender['Confirmed'])
                                <span class="text-primary">
                                    <x-icon name="check" />
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('admin_js')
    <script>

    </script>
@endpush
