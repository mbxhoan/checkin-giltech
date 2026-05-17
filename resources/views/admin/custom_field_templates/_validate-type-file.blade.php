<a href="" class="btn btn-xs btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#validateFile-{{ $customFieldTemplate->id }}"
>
    Ràng buộc
</a>
<div class="modal fade" id="validateFile-{{ $customFieldTemplate->id }}" tabindex="-1" aria-labelledby="validateFile-{{ $customFieldTemplate->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="validateFile-{{ $customFieldTemplate->id }}Label">
                    Chọn loại file ràng buộc cho trường <{{ $customFieldTemplate->description }}>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="row">
                    <div class="col-md-12 text-xs">
                        <p>
                            File phải đúng định dạng và dung lượng không quá <b>2MB</b>.
                        </p>
                    </div>
                </div>
                @foreach (config("info.files.extensions") as $type => $detail)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6>
                                {{ $detail['text'] }}
                            </h6>
                        </div>
                        @foreach ($detail['accepts'] as $mime => $accept)
                            <div class="col-md-4 mb-2">
                                <div class="checkbox text-xs">
                                    <label>
                                        <input type="checkbox"
                                            class="edit-change-field checkbox-value"
                                            name="accepts[]"
                                            id="custom-field-template-{{ $customFieldTemplate->id }}"
                                            value="{{ $accept }}"
                                            @checked(in_array($accept, json_decode($customFieldTemplate->accepts ?? "", true)) ?? false)
                                        >
                                        {{ $accept }}
                                    </label>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>
