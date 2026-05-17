@php
    $index = 1;
@endphp

<div class="bg-light rounded shadow-sm p-2 mb-3">
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "code",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->code ?? $model->generateUniqueCode($model::PREFIX),
            'label'             => "Mã",
            'formClass'         => 'mb-3 col-md-3',
            'required'          => true,
            'readonly'          => true,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "name",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Tên công ty",
            'formClass'         => 'mb-3 col-md-3',
            'placeholder'       => "Tên công ty",
            'required'          => true,
        ])
        <div class="mb-3 col-md-3">
            @include('components.select', [
                'label'         => "Trạng thái",
                'id'            => 'status',
                'fieldName'     => 'status',
                'options'       => $model->getStatues(),
                'selected'      => $model->status,
                'placeholder'   => null,
            ])
        </div>
        <div class="col-md-3 text-end">
            @if (!$model->isNew())
                <a href="{{ route('admin.events.create', [
                        'company_id' => $model->id
                    ]) }}"
                    class="btn btn-sm btn-primary"
                >
                    <i class="fa-regular fa-plus-square fa-fw"></i>
                    Thêm sự kiện
                </a>
            @endif
        </div>
    </div>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "limited_users",
            'value'             => $model->isNew() ? 0 : $model->limited_users,
            'type'              => "number",
            'label'             => "Giới hạn user",
            'formClass'         => 'mb-3 col-md-2',
            'placeholder'       => 10,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "limited_clients",
            'value'             => $model->isNew() ? 0 : $model->limited_clients,
            'type'              => "number",
            'label'             => "Giới hạn data",
            'formClass'         => 'mb-3 col-md-2',
            'placeholder'       => 1000,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "limited_emails",
            'value'             => $model->isNew() ? 0 : $model->limited_emails,
            'type'              => "number",
            'label'             => "Giới hạn email",
            'formClass'         => 'mb-3 col-md-2',
            'placeholder'       => 200,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "limited_events",
            'value'             => $model->isNew() ? 0 : $model->limited_events,
            'type'              => "number",
            'label'             => "Giới hạn sự kiện",
            'formClass'         => 'mb-3 col-md-2',
            'placeholder'       => 10,
        ])
        {{-- <div class="col-md-2 mb-3">
            @include('components.timestamp', [
                'model'             => $model,
            ])
        </div> --}}
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <div class="bg-light rounded shadow-sm p-2 mb-3">
            <div class="form-group mb-3">
                <label class="form-label fw-bold" for="languages">
                    <h5>
                        {{ $index++ }}. Ngôn ngữ
                        <span class="text-secondary text-xs">
                            {{ $model->languages ? count(json_decode($model->languages, true)) : 0 }}/{{ $languages->count() }}
                        </span>
                    </h5>
                </label>
                @foreach ($languages as $language)
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="languages[{{ $language->id }}]" value="{{ $language->id }}"
                                @checked($model->hasLanguage($language->id))
                            >
                                {{ ucfirst($language->description) }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="bg-light rounded shadow-sm p-2 mb-3">
            <div class="form-group mb-3">
                <label class="form-label fw-bold" for="languages">
                    <h5>
                        {{ $index++ }}. Nội dung mail
                        <span class="text-secondary text-xs">
                            {{ $model->templates ? count(json_decode($model->templates, true)) : 0 }}/{{ count($templates) }}
                        </span>
                    </h5>
                </label>
                @foreach ($templates as $template)
                    @if (isset($template['TemplateId']))
                        <div class="checkbox mb-1">
                            <label class="row">
                                <div class="col-md-1">
                                    <input type="checkbox" name="templates[]" value="{{ $template['TemplateId'] }}"
                                        @checked($model->templates ? in_array($template['TemplateId'], json_decode($model->templates, true)) : false)
                                    >
                                </div>
                                <div class="col text-sm">
                                    {{ $template['Alias'] }} - {{ $template['Name'] }}
                                    <a href="{{ route('admin.email_templates.edit-postmark-template', $template['TemplateId']) }}"
                                        target="_blank"
                                        class="text-xs"
                                    >
                                        <x-icon name="edit" />
                                    </a>
                                    <div class="">
                                        Tiêu đề: {{ $template['Subject'] }}
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="bg-light rounded shadow-sm p-2 mb-3">
            <div class="form-group mb-3">
                <label class="form-label fw-bold" for="languages">
                    <h5>
                        {{ $index++ }}. Senders
                        <span class="text-secondary text-xs">
                            {{ $model->senders ? count(json_decode($model->senders, true)) : 0 }}/{{ count($senders) }}
                        </span>
                    </h5>
                </label>
                @foreach ($senders as $sender)
                    <div class="checkbox mb-1">
                        <label class="row">
                            <div class="col-md-1">
                                <input type="checkbox" name="senders[]" value="{{ $sender['ID'] }}"
                                    @checked($model->senders ? in_array($sender['ID'], json_decode($model->senders, true)) : false)
                                >
                            </div>
                            <div class="col-md-11">
                                {{ $sender['Name'] }}
                                <a href="{{ route('admin.email_senders.edit', $sender['ID']) }}"
                                    target="_blank"
                                    class="text-xs"
                                >
                                    <x-icon name="edit" />
                                </a>
                                <div class="text-xs">
                                    {{ $sender['EmailAddress'] }}
                                </div>
                            </div>
                            {{-- <div class="col-md-6 text-xs">
                                {{ $sender['EmailAddress'] }}
                            </div> --}}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="bg-light rounded shadow-sm p-2">
            <div class="form-group mb-3">
                <label class="form-label fw-bold" for="languages">
                    <h5>
                        {{ $index++ }}. Cài đặt
                        @if (!$model->isNew())
                            <a id="btn-sync-settings" href="" class="text-sm sync-config btn-get" title="Đồng bộ cài đặt"
                                data-url="{{ route('admin.companys.sync-event-settings', $model) }}"
                            >
                                <x-icon name="rotate"/>
                            </a>
                        @endif
                    </h5>
                </label>
                <div class="row">
                    @foreach ($settings as $group => $settingMores)
                        <div class="col-md-6 mb-2">
                            <div class="row">
                                <div class="col-md-6 align-items-center">
                                    <h6 class="fw-bold">
                                        <input type="checkbox" class="checkbox-all-settings" id="{{ $group }}" value="">
                                        {{ $group }}
                                        @if (!$model->isNew())
                                            <span class="text-danger text-sm">
                                                {{ isset($currentSettings[$group]) ? count($currentSettings[$group]) : 0 }}
                                            </span>
                                        @endif
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <hr class="my-2">
                                </div>
                            </div>
                            @foreach ($settingMores as $key => $attr)
                                <div class="checkbox">
                                    <div class="row text-xs mb-2">
                                        <div class="col-md-1">
                                            <label>
                                                <input type="checkbox"
                                                    data-group="{{ $group }}"
                                                    class="checkbox-settings checkbox-{{ $group }}"
                                                    id="settings.{{ $group }}.{{ $key }}"
                                                    name="settings[{{ $group }}][{{ $key }}][show]"
                                                    value="1"
                                                    @checked(count($currentSettings) ? (isset($currentSettings[$group][$key]) ? true : false) : ($model->isNew() ? true : false))
                                                >
                                            </label>
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <label class="fw-bold" for="settings.{{ $group }}.{{ $key }}">{{ $attr['name'] }}:</label>
                                        </div> --}}
                                        <div class="col-md-8 px-1">
                                            <input type="text" name="settings[{{ $group }}][{{ $key }}][description]"
                                                id=""
                                                class="w-100"
                                                value="{{ (count($currentSettings) && isset($currentSettings[$group][$key])) ? $currentSettings[$group][$key]['description'] : $attr['description'] }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
