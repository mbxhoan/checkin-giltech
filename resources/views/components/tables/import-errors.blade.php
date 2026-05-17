<div class="{{ $class ?? 'table-responsive' }}">
    <table class="{{ $tableClass ?? 'table table-bordered table-danger' }}">
        <thead>
            <tr>
                <th>Dòng/Tên</th>
                <th>Lỗi/Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($errors as $row => $errorLog)
                <tr>
                    <td class="{{ $tdClass ?? 'text-sm' }}">
                        {{ $row }}
                    </td>
                    <td class="{{ $tdClass ?? 'text-sm' }}">
                        @if (is_array($errorLog) && count($errorLog))
                            @foreach ($errorLog as $index => $error)
                                <div class="">
                                    @if (count($errorLog) > 1)
                                        {{ ++$index }}. {{ $error }}
                                    @else
                                        {{ $error }}
                                    @endif
                                </div>
                            @endforeach
                        @else
                            {{ $errorLog }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
