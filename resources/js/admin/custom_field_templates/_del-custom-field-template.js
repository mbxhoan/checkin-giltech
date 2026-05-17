
export const handleDelCustomFieldTemplate = () => {
  $('.btn-del-template').click(function(event) {
    event.preventDefault();
    var templateId = $(this).data('id');
    var deleteUrl = $(this).data('url'); // Assuming your route prefix is 'admin'
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
        title: "Bạn có chắc muốn xoá?",
        text: "Bạn sẽ không thể khôi phục dữ liệu sau khi thực hiện thao tác này",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: "Xác nhận"
    }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            data: {
                '_token': csrfToken // Include CSRF token for Laravel
            },
            success: function(response) {
              console.log(response);

                if (response.status === 'success') {
                    toastr.success(response.message);
                    $(this).closest('form').remove();
                    $(`form#${templateId}`).remove();
                    $(`form#empty-row input#new.order`).val($(`form#empty-row input#new.order`).val() - 1);
                } else {
                    toastr.success(response.message);
                }
            },
            error: function(e) {
              if (e.responseJSON.message) {
                  toastr.error(e.responseJSON.message);
                  return;
              }

              toastr.error('Đã xảy ra lỗi khi xoá trường thông tin này');
            }
        });
        }
    });

  });

}
