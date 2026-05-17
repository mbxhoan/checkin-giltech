
export const handleEditTranslate = () => {
  $('.edit-translate-field').on('change', function () {
    // console.log($(this).val());
    // console.log($(this).attr('id'));

    // let id = $(this).attr('id');
    // let url = $(this).data('url');
    // let langCode = $(this).data('lang');
    let csrf = $('meta[name="csrf-token"]').attr('content');

    let data = {
      'event_id': $("#event_id").val(),
      'language_id': $("#language_id").val(),
      'name': $(this).attr('name'),
      'value': $(this).val(),
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
            toastr.success(response.message);
          }

          let qrcode = response.data.qrcode;
          $('input#qrcode').val(qrcode)
        }
      },
      error: function (e) {
        if (e.responseJSON.message) {
          toastr.error(e.responseJSON.message);
          return;
        }

        toastr.error('Đã xảy ra lỗi khi');
      }
    });
  });
}
