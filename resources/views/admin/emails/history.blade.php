@extends('admin.layouts.templates.page-index', [
    'pageTitle' => 'Lịch sử gửi mail',
])

@section('title')
    Lịch sử gửi mail {{ $campaign->name }}
@endsection

@section('buttons')
    <div class="buttons">
        @if ($emails->count())
            <a href="{{ route('admin.emails.export-report', [
                    'event'         => $campaign->event,
                    'campaign_id'   => $campaign->id,
                ]) }}"
                class="btn btn-sm btn-success"
            >
                <x-icon name="file-excel" prefix="fa-solid"/>
                @lang('imports.export')
            </a>
        @endif
        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn btn-sm btn-primary">
            <x-icon name="arrow-left" />
            Campaign
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="">
        <h5>
            Tổng số email:
            <span class="text-danger">
                {{ $emails->count() }}
            </span>
        </h5>
        <h6>
            Đã gửi:
            <span class="text-danger">
                {{ $emailsSent->count() }}
            </span>
        </h6>
    </div>
    <p class="text-xs text-secondary">
        Chọn vào email để xem chi tiết lịch sử gửi
        <x-icon name="arrow-down" />
    </p>
    <div id="history-send-mail" class="table table-responsive"
        data-url="{{ route('admin.campaigns.history-table', $campaign) }}"
        data-time="7"
    >
        @include('admin.emails.tables._table-history', [
            'emails' => $emails
        ])
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/emails/history.js'
    ])
@endpush
