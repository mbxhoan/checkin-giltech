@include('components.label_details._single-print', [
    'label'             => $label,
    'labelDetails'      => $labelDetails,
    'client'            => $client ?? null,
])
@if ($event && !empty($clients))
    @include('components.label_details._multi-print', [
        'label'         => $label,
        'client'        => $client,
        'clients'       => $clients,
        'labelDetails'  => $labelDetails,
    ])
@endif
@include('components.label_details.style', [
    'label'             => $label
])
