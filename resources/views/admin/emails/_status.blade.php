<span class="{{ $class ?? 'btn btn-xs' }} {{ $email->getStatusClass() }}">
    {{ $email->getStatusText() }}
</span>
