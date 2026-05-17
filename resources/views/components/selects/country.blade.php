@if (!empty($countrys))
    <div class="country">
        @include('common.controls.selects2', [
            'values' => [
                'country' => [
                    'fieldName'     => 'country_id',
                    'label'         => __(strtolower($model->code).'.country'),
                    'id'            => 'select-country',
                    'options'       => !empty($countrys) ? $countrys : [],
                    'selected'      => !empty($defaultCountry) ? $defaultCountry->id : null,
                    'className'     => 'input-country',
                    'required'      => true,
                ],
            ]
        ])
    </div>
@endif
