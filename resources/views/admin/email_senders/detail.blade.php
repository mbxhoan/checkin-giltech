@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => "Chỉnh sửa",
    'colLeft'       => 'col-md-8',
    'colRight'      => 'col-md-6',
])

@section('form-action', route('admin.email_senders.update', $sender->ID))
@section('form-back', route('admin.email_senders.index'))

@section('buttons')
    <div class="">

    </div>
@endsection

@section('primary-content')
    @include('admin.email_senders._form', [
        'sender' => $sender
    ])
@endsection

@section('secondary-content')

@endsection

@push('admin_js')

@endpush

@push('admin_css')

@endpush
