<div class="collapse p-2 bg-light rounded shadow-sm" id="collapseCheckin">
    <div class="row mb-2 justify-content-center">
        <div class="col-md-4 fw-bold text-sm text-center">
            <a href="">
                <a class="btn btn-sm btn-primary" href="{{ route('admin.checkins.config', $event) }}">
                    Cấu hình Checkin
                </a>
            </a>
        </div>
        <div class="col-md-4 fw-bold text-sm text-center">
            <a href="">
                <a class="btn btn-sm btn-primary" target="_blank" href="{{ $event->getScanLink() }}">
                    Trang Checkin
                </a>
            </a>
        </div>
    </div>

    {{-- event areas --}}
    @if (!$event->isNew())
        <div class="row mb-3">
            <div class="col-12">
                <h6>Khu vực Checkin</h6>
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Mô tả</th>
                            <th class="text-center">Nhóm khách</th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($event->areas as $area)
                            <tr>
                                <form action="{{ route('admin.event_areas.update', [
                                        'event_area' => $area
                                    ]) }}" method="POST"
                                >
                                    @csrf
                                    @method('PUT')
                                    <td>{{ $area->name }}</td>
                                    <td>
                                        <input type="text" name="description" class="form-control form-control-sm" value="{{ $area->description }}" placeholder="Mô tả">
                                    </td>
                                    <td>
                                        @foreach ($clientTypes as $i => $name)
                                            <div class="">
                                                <input type="checkbox" name="client_types[]" id="{{ $area->id }}-{{ $i }}"
                                                    value="{{ $i }}" @checked(!empty($area->client_types) ? in_array($i, $area->client_types) : false)
                                                >
                                                <label for="{{ $area->id }}-{{ $i }}" class="text-xs">{{ $name }}</label>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-center" colspan="2">
                                        <button type="submit" class="btn btn-xs btn-primary">Lưu</button>
                                    </td>
                                </form>
                                {{-- <td class="text-center">
                                    <form action="{{ route('admin.event_areas.destroy', [$event, $area]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa khu vực này?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Xóa</button>
                                    </form>
                                </td> --}}
                            </tr>
                        @endforeach
                        <tr>
                            <form action="{{ route('admin.event_areas.store') }}" method="POST" class="d-flex">
                                @csrf
                                <td>
                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Tên khu vực mới" required>
                                </td>
                                <td>
                                    <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả">
                                </td>
                                <td class="text-center" colspan="3">
                                    <button type="submit" class="btn btn-xs btn-primary">Lưu</button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

