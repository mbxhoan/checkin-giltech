"use-strict";

$(document).ready(function() {
    // Khởi tạo Select2 cho dropdown cơ cấu nhiều người
    if ($.fn.select2) {
        $('.input-assignee_ids').select2({
            placeholder: 'Abc',
            allowClear: true,
            width: '100%'
        });
    }

    // Reset kết quả quay (xóa reward_id của tất cả client)
    $('#resetRewardClient').on('click', function(e) {
        e.preventDefault();

        const luckyDrawId = $(this).data('lucky-draw-id');

        if (!confirm('Bạn có chắc muốn RESET tất cả kết quả quay thưởng?\nTất cả người đã trúng sẽ được xóa kết quả.')) {
            return;
        }

        $.ajax({
            url: '/admin/lucky_draw_clients/reset',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                lucky_draw_id: luckyDrawId
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra khi reset kết quả quay');
            }
        });
    });

    // Xóa tất cả giải thưởng
    $('#resetButton').on('click', function(e) {
        e.preventDefault();

        const luckyDrawId = $(this).data('lucky-draw-id');
        const confirmText = prompt('Nhập "RESET" để xác nhận xóa TẤT CẢ giải thưởng:');

        if (confirmText !== 'RESET') {
            if (confirmText !== null) {
                alert('Xác nhận không đúng. Vui lòng nhập đúng "RESET"');
            }
            return;
        }

        $.ajax({
            url: `/admin/lucky_draw_rewards/reset/${luckyDrawId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                confirm: 'RESET'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra khi xóa giải thưởng');
            }
        });
    });

    // Huỷ giải cho 1 người cụ thể
    $(document).on('click', '.btn-cancel-winner, .btn-cancel-reward', function(e) {
        e.preventDefault();

        const rewardId = $(this).data('id');
        const url = $(this).data('url');

        if (!confirm('Bạn có chắc muốn huỷ giải thưởng này?')) {
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                reward_id: rewardId
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra khi huỷ giải');
            }
        });
    });

    // Tự động lưu khi chọn người được gán (assignee_id)
    $(document).on('change', '.input-assignee_id', function(e) {
        e.preventDefault();
        
        const selectElement = $(this);
        const assigneeId = selectElement.val();
        const hiddenInput = selectElement.closest('.col-md-4').find('.data-assignee_id');
        const url = hiddenInput.data('url');
        
        if (!url) {
            console.error('URL không tìm thấy');
            return;
        }

        // Disable select trong khi đang lưu
        selectElement.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                assignee_id: assigneeId || null
            },
            dataType: 'json',
            success: function(response) {
                // Hiển thị thông báo thành công
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Đã cập nhật người được gán thành công');
                } else {
                    alert(response.message || 'Đã cập nhật người được gán thành công');
                }
                selectElement.prop('disabled', false);
            },
            error: function(xhr) {
                // Khôi phục giá trị cũ nếu lỗi
                const oldValue = hiddenInput.data('old-value');
                if (oldValue !== undefined) {
                    selectElement.val(oldValue);
                }
                
                const errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi cập nhật người được gán';
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
                selectElement.prop('disabled', false);
            }
        });
    });

    // Lưu giá trị cũ khi focus vào select để có thể khôi phục nếu lỗi
    $(document).on('focus', '.input-assignee_id', function() {
        const hiddenInput = $(this).closest('.col-md-4').find('.data-assignee_id');
        hiddenInput.data('old-value', $(this).val());
    });

    // Tự động lưu khi chọn nhiều người được gán (assignee_ids)
    $(document).on('change', '.input-assignee_ids', function(e) {
        e.preventDefault();

        const selectElement = $(this);
        const assigneeIds = selectElement.val() || [];
        const hiddenInput = selectElement.closest('.col-md-4').find('.data-assignee_ids');
        const url = hiddenInput.data('url');
        const maxWinners = parseInt(hiddenInput.data('max'), 10) || 0;

        if (!url) {
            console.error('URL không tìm thấy');
            return;
        }

        // Validate max assignees by reward value
        if (maxWinners > 0 && assigneeIds.length > maxWinners) {
            const oldValue = hiddenInput.data('old-value');
            if (oldValue !== undefined) {
                selectElement.val(oldValue);
            }
            const msg = `Giải này chỉ cho phép abc tối đa ${maxWinners} người`;
            if (typeof toastr !== 'undefined') {
                toastr.error(msg);
            } else {
                alert(msg);
            }
            return;
        }

        // Disable select trong khi đang lưu
        selectElement.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                assignee_ids: assigneeIds
            },
            dataType: 'json',
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Đã cập nhật danh sách người được gán thành công');
                } else {
                    alert(response.message || 'Đã cập nhật danh sách người được gán thành công');
                }
                // Update old value after successful save
                hiddenInput.data('old-value', selectElement.val());
                selectElement.prop('disabled', false);
            },
            error: function(xhr) {
                const oldValue = hiddenInput.data('old-value');
                if (oldValue !== undefined) {
                    selectElement.val(oldValue);
                }

                const errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi cập nhật danh sách người được gán';
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
                selectElement.prop('disabled', false);
            }
        });
    });

    // Lưu giá trị cũ khi focus vào multi-select để có thể khôi phục nếu lỗi
    $(document).on('focus', '.input-assignee_ids', function() {
        const hiddenInput = $(this).closest('.col-md-4').find('.data-assignee_ids');
        hiddenInput.data('old-value', $(this).val());
    });
});
