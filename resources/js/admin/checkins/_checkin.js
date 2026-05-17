"use-strict";

export const checkin = (eventCode, qrcode) => {
  let token = $('meta[name="csrf-token"]').attr('content');
  let data = {
      'qrcode': qrcode,
      'event_code': eventCode,
      '_token': token
  };

  console.log(data);

  $.ajax({
    url: '/admin/checkins/checkin',
    method: 'POST',
    data: data,
    headers: {
      'X-CSRF-TOKEN': $('input[name="_token"]').val()
    },
    success: function (response) {
      console.log(response);
      toastr.success("Đã checkin thành công");
    },
    error: function (xhr) {
      if (xhr.status === 422) {
        let errors = xhr.responseJSON.message;
        for (const field in errors) {
          if (errors.hasOwnProperty(field)) {
            errors[field].forEach(errorMsg => {
              toastr.error(errorMsg); // your custom or library toast function
            });
          }
        }
      } else {
        toastr.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
      }
    }
  });
}
