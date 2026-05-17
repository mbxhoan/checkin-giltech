<form id="form-export" action="{{ $route }}" class="form-inline"
>
    @csrf
    @if (isset($fields))
        @foreach ($fields as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
        @endforeach
    @endif
    <button type="submit"
        class="btn btn-success btn-sm btn-submit-form align-self-center mb-lg-0 mb-2"
    >
        <x-icon name="file-excel" prefix="fa-solid"/>
        {{ $text ?? __('imports.export') }}
    </button>
</form>
