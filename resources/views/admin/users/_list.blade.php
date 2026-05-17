<table class="table table-hover table-sm">
    <caption>{{ trans_choice('users.count', $users->total()) }}</caption>
    <thead>
        <tr>
            <th class="col">#</th>
            <th>@lang('users.attributes.name')</th>
            <th>Username</th>
            <th class="col-2">@lang('users.attributes.email')</th>
            <th>@lang('users.attributes.status')</th>
            <th>@lang('users.attributes.type')</th>
            @sys_admin
                <th>Gói sử dụng</th>
                <th>Công ty</th>
            @endsys_admin
            <th>@lang('users.attributes.event_id')</th>
            <th>@lang('users.attributes.expire_date')</th>
            <th>@lang('users.attributes.updated_at')</th>
            <th>@lang('users.attributes.registered_at')</th>
            <th>@lang('users.attributes.last_login_at')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $index => $user)
            <tr data-href="{{ route('admin.users.edit', $user) }}"
                class="{{ $user->checkIfNotExpired() ? "" : "table-danger" }} {{ $user->email_verified_at ? "" : "table-warning" }} text-sm"
            >
                <th class="fw-bold text-center text-sm">
                    {{ $user->id }}
                </th>
                <td class="">
                    {{ $user->fullname }}
                </td>
                <td>
                    <span id="user-username-{{ $user->id }}">{{ $user->username }}</span>
                    @include('components.btn-copy', [
                        'class'     => '',
                        'targetId'  => "user-username-{$user->id}"
                    ])
                </td>
                <td class="">
                    <span id="user-email-{{ $user->id }}">{{ $user->email }}</span>
                    @include('components.btn-copy', [
                        'class'     => '',
                        'targetId'  => "user-email-{$user->id}"
                    ])
                </td>
                {{-- <td class="fw-bold text-sm text-{{ $user->getTypeColor() }}"> --}}
                <td>
                    <span class="btn btn-xs {{ $user->getStatusClass() }}">
                        {{ $user->getStatusText() }}
                    </span>
                </td>
                <td class="text-sm">
                    {{-- <div class="fw-bold">
                        {{ $user->getTypeText() }}
                    </div> --}}
                    @foreach ($user->roles as $role)
                        <div class="text-xs fst-italic bg-info px-1 mb-1">
                            {{ $role->name }}
                        </div>
                    @endforeach
                </td>
                @sys_admin
                    <td class="fw-bold">
                        {{ $user->package_id ? config("info.packages.{$user->package->code}.name") : null }}
                    </td>
                    <td class="fw-bold">
                        @if ($user->company_id)
                            <a href="{{ route('admin.companys.edit', [
                                    'company' => $user->company
                                ]) }}"
                            >
                                {{ $user->company->code }}
                                @if ($user->company->status == $user->company::STATUS_NEW)
                                    <span class="text-danger">
                                        <x-icon name="fire" prefix="fa-solid fa-bounce" />
                                    </span>
                                @endif
                            </a>
                        @endif
                    </td>
                @endsys_admin
                <td class="fw-bold">
                    @if (!empty($user->event_id))
                        <a target="_blank" href="{{ route('admin.events.edit', [
                                'event' => $user->event,
                            ]) }}" class=""
                        >
                            {{ $user->event->code }}
                        </a>
                    @endif
                </td>
                <td class="{{ $user->checkIfNotExpired() ? "" : "text-danger fw-bold" }}">
                    {{ $user->expire_date ? humanize_date($user->expire_date, 'd/m/Y') : null }}
                </td>
                <td>
                    @humanize_date($user->updated_at, 'd/m/Y H:i:s')
                </td>
                <td>
                    @humanize_date($user->registered_at, 'd/m/Y H:i:s')
                </td>
                <td>
                    @if ($user->last_login_at)
                        @humanize_date($user->last_login_at, 'd/m/Y H:i:s')
                        <div class="">
                            <form action="{{ route('admin.users.sign-out', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-danger btn-submit-form mb-2" title="Đăng xuất tài khoản">
                                    <x-icon name="right-from-bracket" />
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    @else
                        <span class="text-sm fw-bold fst-italic">
                            @lang('users.not_login_yet')
                        </span>
                    @endif
                </td>
                <td>
                    @if (!$user->email_verified_at)
                        @if ($user->verify_token)
                            <form action="{{ route('admin.users.send-verification', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-xs text-white btn-warning btn-submit-form mb-2" title="Xác nhận tài khoản">
                                    <x-icon name="circle-check" />
                                    Gửi xác thực
                                </button>
                            </form>
                        @else
                            <div class="fst-italic text-xs">
                                Chưa có token
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $users->links() }}
</div>
