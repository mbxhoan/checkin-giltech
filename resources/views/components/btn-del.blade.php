@if ($route)
    <form
        action="{{ $route }}"
        method="POST" class="form-inline" data-confirm="{{ $confirm ?? __('forms.common.delete') }}">
        @method('DELETE')
        @csrf

        <button type="submit" name="submit" class="{{ $class ?? 'btn btn-danger btn-sm' }}">
            <x-icon name="trash" />
        </button>
    </form>
@endif
