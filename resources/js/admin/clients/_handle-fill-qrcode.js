
export const handleFillQrcode = () => {
  fillQrcode($('#btn-fill-qrcode'));

  $('#btn-fill-qrcode').click(function (event) {
    event.preventDefault();
    fillQrcode($(this));
  });
}

const fillQrcode = (btn) => {
  let customFields = {};
  let url = btn.data('url');
  console.log(url);

  $('.custom-field').each(function () {
    const fullName = $(this).attr('name'); // e.g., custom_fields[company]
    const match = fullName.match(/^custom_fields\[(.+)\]$/);
    const name = match ? match[1] : fullName;
    const value = $(this).val();
    customFields[name] = value;
  });

  let data = {
    'email': $('input#email').val(),
    'name': $('input#name').val(),
    custom_fields: customFields,
  }

  $.ajax({
    url: url,
    type: 'GET',
    data: data,
    success: function (response) {
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
}
