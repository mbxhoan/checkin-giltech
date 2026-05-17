
export const handleToggleLanguageSelection = () => {
  $('.toggle-language-selection').on('change', function () {
    let isChecked = $(this).is(':checked');
    let isShow = isChecked ? true : false;
    let csrf = $('meta[name="csrf-token"]').attr('content');
    let data = {
      'is_show':isShow,
      '_token': csrf
    }

    $.ajax({
      url: $(this).data('url'),
      type: 'POST',
      data: data,
      success: function (response) {
        console.log(response);
        if (response.status === 'success') {
          if (response.message != '') {
            // toastr.success(response.message);
          }
        }
      },
      error: function (e) {
        if (e.responseJSON.message) {
          toastr.error(e.responseJSON.message);
          return;
        }

        toastr.error('Đã xảy ra lỗi khi cập nhật');
      }
    });
  });
}
