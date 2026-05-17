<form action="{{ route('admin.campaigns.clone', $model) }}" method="post">
    @csrf
    <button type="submit" class="btn btn-sm btn-primary">
        <i class="fa-solid fa-copy"></i>
        Sao chép
    </button>
</form>
