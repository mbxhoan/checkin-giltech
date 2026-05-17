<form action="{{ !$user->isNew() ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
    @if ($user->isNew())
        @method('POST')
    @else
        @method('PATCH')
    @endif

    @csrf

    <div class="row">
        @sys_admin
            <div class="mb-3 col-md-3">
                @include('components.select', [
                    'label'         => $user->company_id ?
                        '<a href="'.route('admin.companys.edit', $user->company).'" target="_blank">Công ty <i class="fa-solid fa-edit fa-xs"></i></a>' :
                        "Công ty",
                    'fieldName'     => 'company_id',
                    'id'            => 'company_id',
                    'options'       => $companyArray,
                    'selected'      => request()->company_id ?? $user->company_id,
                    'placeholder'   => null,
                    'required'      => true,
                    // 'changeUrl'     => route('admin.events.get-list-by-company-id'),
                ])
            </div>
        @else
            @include('components.form-groups.input-group', [
                'id'                => "company_id",
                'fieldName'         => "company_id",
                'value'             => $company->id,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
        @endsys_admin

        @include('components.form-groups.input-group', [
            'id'                => "name",
            'model'             => $user,
            'type'              => "text",
            'label'             => __('users.attributes.name'),
            'placeholder'       => __('users.placeholder.name'),
            'required'          => true,
            'formClass'         => 'mb-2 col-md-3',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "email",
            'model'             => $user,
            'type'              => "email",
            'label'             => __('users.attributes.email'),
            'placeholder'       => __('users.placeholder.email'),
            'required'          => $user->isNew() ? true : ($user),
            'formClass'         => 'mb-2 col-md-2',
            'readonly'          => $user->isNew() ? false : true,
            'disabled'          => $user->isNew() ? false : true,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "username",
            'model'             => $user,
            'type'              => "text",
            'label'             => "Username",
            'placeholder'       => "Username",
            'required'          => true,
            'formClass'         => 'mb-2 col-md-2',
            'readonly'          => $user->isNew() ? false : true,
            'disabled'          => $user->isNew() ? false : true,
        ])
        @include('components.form-groups.input-group', [
            'fieldName'     => "is_checkout",
            'id'            => "is_checkout",
            'model'         => $user,
            'label'         => 'Checkout',
            'showLabelTop'  => true,
            'type'          => "toggle",
            'checked'       => $user->is_checkout,
            'value'         => 1,
            'formClass'     => 'mb-2 col-md-2',
            'inputClass'    => 'form-check-input text-sm',
        ])
    </div>
    <div class="row">
        <div class="mb-3 col-md-3">
            @include('components.select', [
                'label'         => $user->event_id ?
                    '<a href="'.route('admin.events.edit', $user->event).'" target="_blank">Sự kiện <i class="fa-solid fa-edit fa-xs"></i></a>' :
                    "Sự kiện",
                'fieldName'     => 'event_id',
                'id'            => 'event_id',
                'options'       => $eventArray,
                'selected'      => $user->event_id ?? ($event->id ?? null),
                'placeholder'   => null,
            ])
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "password",
            'model'             => null,
            'type'              => "password",
            'label'             => __('users.attributes.password'),
            'formClass'         => 'form-group mb-3 col-md-3',
            'inputClass'        => 'form-control text-sm',
            'placeholder'       => __('users.attributes.password'),
        ])
        @include('components.form-groups.input-group', [
            'id'                => "password_confirmation",
            'model'             => null,
            'type'              => "password",
            'label'             => __('users.attributes.password_confirmation'),
            'formClass'         => 'form-group mb-3 col-md-3',
            'inputClass'        => 'form-control text-sm',
            'placeholder'       => __('users.attributes.password_confirmation'),
        ])
        <div class="mb-3 col-md-2">
            @include('components.select', [
                'label'         => __('users.attributes.status'),
                'fieldName'     => 'status',
                'id'            => 'status',
                'options'       => $user->getStatues(),
                'selected'      => $user->status,
                'placeholder'   => null,
                'required'      => true,
            ])
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-2">
                <label class="form-label" for="roles">
                    @lang('users.attributes.roles')
                </label>

                @foreach ($roles as $role)
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="roles[{{ $role->id }}]" value="{{ $role->id }}"
                                @checked($user->hasRole($role->name))>

                            @if (Lang::has('roles.' . $role->name))
                                {!! __('roles.' . $role->name) !!}
                            @else
                                {{ ucfirst($role->name) }}
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            @sys_admin
                @include('components.select', [
                    'label'         => "Gói đang dùng",
                    'fieldName'     => 'package_id',
                    'id'            => 'package_id',
                    'options'       => $packagesArray,
                    'selected'      => $user->package_id,
                    'placeholder'   => null,
                    'formClass'     => 'mb-3 form-control'
                ])
            @else
                @include('components.form-groups.input-group', [
                    'id'                => "package_id",
                    'type'              => "hidden",
                    'value'             => auth()->user()->package_id,
                    'formClass'         => 'd-none'
                ])
            @endsys_admin
        </div>
        <div class="mb-3 col-md-2">
            @include('components.form-groups.input-group', [
                'id'                => "expire_date",
                'model'             => $user,
                'type'              => "date",
                'value'             =>  $user->expire_date ? humanize_date($user->expire_date, 'Y-m-d') : null,
                'label'             => "Ngày hết hạn",
                'formClass'         => ''
            ])
            @if ($user->login_code)
                {{-- <div class="mt-2">
                    <a href="" class="w-100" target="_blank">
                        <img src="" alt="" width="100">
                    </a>
                    <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#img_qrcode-{{ $user->id }}">
                        <x-icon name="clipboard" prefix="fa-regular" />
                    </button>
                </div> --}}

                @php
                    $qr = base64_encode(QrCode::format('png')
                        ->size(400)
                        ->margin(2)
                        ->merge(public_path('assets/images/delfi-logo.png'), ".3", true)
                        ->errorCorrection('Q')
                        ->generate($user->login_code));
                @endphp

                <div class="text-center mt-2 w-100">
                    @if (!empty($user) && !$user->isNew())
                        <a href="{{ route('admin.users.generate-login-qrcode', ['user' => $user]) }}" class="text-xs mb-2">
                            Tạo mới Qrcode Đăng nhập
                        </a>
                    @endif
                    {{-- QR Image --}}
                    <a href="#" onclick="copyLink()">
                        <img id="qrImage"
                            src="data:image/png;base64,{{ $qr }}"
                            class="border rounded shadow-sm"
                            style="cursor:pointer;"
                            width="100%"
                        />
                    </a>
                    <div class="mt-3">
                        <button onclick="copyImage()" type="button" class="btn btn-outline-primary btn-xs mb-1">Copy Image</button>
                        <button onclick="copyLink()" type="button" class="btn btn-outline-info btn-xs mb-1">Copy Code</button>
                        <a href="data:image/png;base64,{{ $qr }}" download="qrcode.png" class="btn btn-outline-success btn-xs mb-1">Download</a>
                        <br>
                        <button onclick="copyUsername()" type="button" class="btn btn-outline-info btn-xs mb-1">Copy Username</button>
                        {{-- <a href="{{ $user->login_code }}" target="_blank" class="btn btn-warning btn-xs mb-1">Open Link</a> --}}
                    </div>
                </div>
            @else
                @if (!empty($user) && !$user->isNew())
                    <a href="{{ route('admin.users.generate-login-qrcode', ['user' => $user]) }}" class="text-sm">
                        Tạo Qrcode Đăng nhập
                    </a>
                @endif
            @endif
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "gate",
            'model'             => $user,
            'type'              => "text",
            'value'             =>  $user->gate ? humanize_date($user->gate, 'Y-m-d') : null,
            'label'             => "Quầy/Gian hàng",
            'placeholder'       => "VIP,...",
            'formClass'         => 'mb-3 col-md-2'
        ])
        @if (!empty($areasArray))
            <div class="col-md-2">
                @include('components.select', [
                    'label'         => 'Khu vực checkin',
                    'fieldName'     => 'area_id',
                    'id'            => 'area_id',
                    'options'       => $areasArray,
                    'selected'      => $user->area_id,
                    'placeholder'   => null,
                    // 'required'      => true,
                ])
            </div>
        @endif
        <div class="col-md-2">
            @include('components.select', [
                'label'         => __('users.attributes.gender'),
                'fieldName'     => 'gender',
                'id'            => 'gender',
                'options'       => $user->getGenders(),
                'selected'      => $user->gender,
                'placeholder'   => null,
                'required'      => true,
            ])
        </div>
        <div class="mb-3 col-md-2 d-none">
            @include('components.select', [
                'label'         => __('users.attributes.type'),
                'fieldName'     => 'type',
                'id'            => 'type',
                'options'       => $user->getTypes(),
                'selected'      => $user->type,
                'placeholder'   => null,
                'required'      => true,
            ])
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-light">
        <x-icon name="chevron-left" />

        @lang('forms.actions.back')
    </a>

    <button type="submit" class="btn btn-primary">
        <x-icon name="save" />

        @lang('forms.actions.update')
    </button>
</form>

@push('admin_js')
    <script>
        function copyLink() {
            navigator.clipboard.writeText("{{ $user->login_code }}")
                .then(() => alert("Copied code!"))
                .catch(() => alert("Copy failed"));
        }

        function copyUsername() {
            navigator.clipboard.writeText("{{ $user->username }}")
                .then(() => alert("Copied username!"))
                .catch(() => alert("Copy failed"));
        }

        function copyImage() {
            const img = document.getElementById('qrImage');

            fetch(img.src)
                .then(res => res.blob())
                .then(blob => {
                    const item = new ClipboardItem({ "image/png": blob });

                    navigator.clipboard.write([item]).then(() => {
                        alert("Copied image!");
                    });
                })
                .catch(() => alert("Copy image failed"));
        }
    </script>
@endpush
