<form id="form-export" action="{{ route('admin.clients.export-list', [
        'event' => $event
    ]) }}" class="form-inline"
>
    @csrf
    @if (isset($fields))
        @foreach ($fields as $field => $value)
            @if (is_scalar($value) || is_null($value))
                <input type="hidden" name="{{ $field }}" value="{{ $value }}">
            @elseif (is_array($value))
                @foreach ($value as $v)
                    <input type="hidden" name="{{ $field }}[]" value="{{ $v }}">     
                @endforeach
            @endif           
        @endforeach
    @endif
    <button type="submit"
        class="btn btn-success btn-sm btn-submit-form align-self-center mb-lg-0 mb-2"
    >
        <x-icon name="file-excel" prefix="fa-solid"/>
        @lang('imports.export')
    </button>
</form>
