<form action="{{ route('admin.events.remove-feature', $model) }}" method="POST" class="form-inline">
    @csrf
    <input type="hidden" name="feature" value="{{ $value }}">
    <button type="submit" class="btn text-danger p-0" title="Ẩn tính năng">
        <x-icon name="minus" />
    </button>
</form>
