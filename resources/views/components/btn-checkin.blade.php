@if ($route)
    @php
        $scanType = $type ?? \App\Models\Checkin::TYPE_CHECKIN;
        $isCheckoutButton = $scanType === \App\Models\Checkin::TYPE_CHECKOUT;
        $hasCheckin = !empty($model->findCheckin());
        $isChecked = $isCheckoutButton ? !empty($model->findCheckout()) : $hasCheckin;
    @endphp

    <form
        action="{{ $route }}"
        method="POST"
        class="form-inline {{ $isCheckoutButton ? 'js-checkout-form' : '' }}"
        data-confirm="{{ $confirm ?? null }}"
        data-has-checkin="{{ $hasCheckin ? 1 : 0 }}"
    >
        @csrf
        @include('components.form-groups.input-group', [
            'id'                => "event_code",
            'fieldName'         => "event_code",
            'value'             => $model->event_code,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "qrcode",
            'fieldName'         => "qrcode",
            'value'             => $model->qrcode,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "type",
            'fieldName'         => "type",
            'value'             => $scanType,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @if ($isChecked)
            <button type="button" class="btn btn-secondary btn-xs">
                <x-icon name="check" />
                {{ $text ?? null }}
            </button>
        @else
            <button type="submit" name="submit" class="{{ $class ?? 'btn btn-success btn-sm' }}">
                <x-icon name="check" />
                {{ $text ?? null }}
            </button>
        @endif
    </form>
@endif
