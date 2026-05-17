
export const handleChangeStatus = () => {
  $('.btn-change-status').off('click').on('click', function (e) {
    e.preventDefault();
    const $button = $(this);
    const id = $button.data('id');
    const $buttons = $(`.btns-${id}`);
    const url = $button.data('url');
    const targetStatus = $button.data('target_status');
    const buttonHtml = $button.html();

    // Add loading state to the button
    $buttons.html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>')
    $buttons.addClass('disabled');
    $buttons.prop('disabled', true);

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            status: targetStatus,
            _token: $('meta[name="csrf-token"]').attr('content') // make sure CSRF token is in your layout
        },
        success: function (response) {
          $buttons.html(buttonHtml)
          $buttons.removeClass('disabled');
          $buttons.prop('disabled', false);

          if (response.status === 'success') {
            if (response.message != '') {
              // toastr.success(response.message);
            }

            $(`#email-status-${id}`).html(response.data.html1);
            $(`#btns-status-${id}`).html(response.data.html2);
            handleChangeStatus();

            if (targetStatus == "SENT") {
              $(`#email-sent_at-${id}`).html(response.data.sent_at);
            }
          }
        },
        error: function (e) {
          $buttons.html(buttonHtml)
          $buttons.removeClass('disabled');
          $buttons.prop('disabled', false);

          if (e.responseJSON.message) {
            toastr.error(e.responseJSON.message);
            return;
          }

          toastr.error('Đã xảy ra lỗi khi');
        }
    });
  });
}
