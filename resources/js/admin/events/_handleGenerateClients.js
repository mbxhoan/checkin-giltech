"use-strict";

// import _btnModules from "../common/_btnModules";

export const handleGenerateClients = () => {
  $('#btn-generate-clients').on('click', function (e) {
    e.preventDefault();
    let confirm = $(this).data('confirm');
    var url = $(this).data('url'); // Assuming your route prefix is 'admin'
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let btn = this;
    let btnHtml = $(this).html();

    Swal.fire({
      title: confirm,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "Xác nhận"
    }).then((result) => {
      if (result.isConfirmed) {
        // $('#btn-generate-clients').html();
        // _btnModules.getBtnWaiting('#btn-generate-clients');
        $(btn).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading')
        $(btn).addClass('disabled')

        $.ajax({
          url: url,
          type: 'POST',
          data: {
            'count': $('#count').val(),
            'type': $('#type').val(),
            '_token': csrfToken // Include CSRF token for Laravel
          },
          success: function (response) {
            console.log(response);
            $(btn).html(btnHtml)
            $(btn).removeClass('disabled')

            if (response.status === 'success') {
              toastr.success(response.message);
              if (response.data.redirectTo) {
                window.location.href = response.data.redirectTo;
              }
            }
          },
          error: function (e) {
            $(btn).html(btnHtml)
            $(btn).removeClass('disabled')
            
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
