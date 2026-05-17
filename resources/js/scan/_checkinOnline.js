import { checkinOffline } from "./_checkinOffline";
import { loading } from "./_loading";
import { renderError } from "./_renderError";
import { renderLabel } from "./_renderLabel";
import { renderSuccess } from "./_renderSuccess";

export const checkinOnline = (code, isToast = false, byPass = 0, checkoutAction = null) => {
  console.log("Scanned code:", code);
  loading();

  let completion = false;
  let timeoutToggleCheckinOffline = 3000;
  let eventCode = $('#event_code').val();
  let token = $('meta[name="csrf-token"]').attr('content');
  let data = {
    'qrcode': code,
    'event_code': eventCode,
    'by_pass_duplicate': byPass,
    '_token': token,
  };
  if (checkoutAction) {
    data.checkout_action = checkoutAction;
  }

  $.ajax({
    url: '/checkin',
    type: 'POST',
    data: data,
    success: function (response) {
      if (response.status === 'success') {
        if (isToast && response.message != '') {
          toastr.success(response.message);
        }

        let checkin = response.data.checkin;
        let fields = null;
        let msg = response.message;
        completion = true;

        if (response.data.requires_checkout_confirm) {
          hideLoadingOverlay();
          confirmCheckoutAction(code);
          return;
        }

        if (response.data.fields) {
          fields = response.data.fields;
        }

        if (checkin) {
          renderSuccess(fields, msg, '#msg-success');
          const shouldPrint = (eventCode === 'hnt4') ? !!(response.data.should_print) : true;
          if (shouldPrint) {
            renderLabel(fields.id);
          }
          /* next-level */
          if (eventCode === "next-level") {
            confirmYesNo(eventCode, code, fields.name);
          }
        } else {

          /* customize */
          /* galaxy-holding */
          /* tes-812 */

          if (response.data.is_duplicated) {
            if (["galaxy-holding", "tes-812"].includes(eventCode)) {
              confirmCheckinByPassDuplicate(code, fields ?? {}, msg);
            } else {
              renderSuccess(fields, msg, '#msg-duplicated');
            }
          } else {
            console.log("Check-in Error Result:", response);
            if (eventCode === 'hnt4') {
                console.log("Qrcode:", code);
                console.log("Client Info:", fields);
                console.log("Error Message:", msg);
            }
            renderError(fields, msg);
          }
        }
      }
    },
    error: function (e, textStatus, errorThrown) {
      console.error('AJAX error:', textStatus, errorThrown);

      // Handle known error scenarios
      if (e.responseJSON && e.responseJSON.message) {
        let msg = e.responseJSON.message;
        console.error(msg);
        completion = true;

        if (isToast && msg) {
          toastr.error(msg);
        }

        renderError(null, msg);
        return;
      }

      // Handle network errors like internet disconnected
      if (textStatus === 'error' && !e.responseJSON) {
        const networkErrorMsg = 'Không thể kết nối đến máy chủ. Kiểm tra kết nối mạng hoặc thử lại sau.';
        console.error(networkErrorMsg);
        toastr.error(networkErrorMsg);
        completion = false;
        // transferOfflineMode(code);
        // return;
        // renderError(null, networkErrorMsg);
      }
    }
  });

  setTimeout(function () {
    if (!completion) {
      transferOfflineMode(code);
    }
  }, timeoutToggleCheckinOffline);
}

const transferOfflineMode = (qrcode) => {
  console.info("*** Toggle OFFLINE MODE ***");
  $('#toggle_online').prop('checked', false);
  $('#offline-offcanvas').addClass('text-danger');
  checkinOffline(qrcode);
}

const hideLoadingOverlay = () => {
  $('.overlay-layer').hide();
}

const confirmCheckoutAction = (qrcode) => {
  Swal.fire({
    title: 'Mã này chưa checkin',
    text: 'Bạn muốn chỉ checkout hay checkin và checkout cùng lúc?',
    icon: 'question',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonColor: '#198754',
    denyButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Checkin và checkout',
    denyButtonText: 'Chỉ checkout',
    cancelButtonText: 'Huỷ',
  }).then((result) => {
    hideLoadingOverlay();

    if (result.isConfirmed) {
      checkinOnline(qrcode, false, 0, 'checkin_and_checkout');
      return true;
    }

    if (result.isDenied) {
      checkinOnline(qrcode, false, 0, 'checkout_only');
      return true;
    }

    $('#qrcode').focus();
    return false;
  });
}

/* next-level */
const confirmYesNo = (eventCode, qrcode, name) => {
  Swal.fire({
    title: `Dear Ông/Bà ${name},`,
    text: "Ông/Bà có cho phép chúng tôi được sử dụng hình ảnh tại sự kiện của chúng tôi?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "ĐỒNG Ý",
    cancelButtonText: "KHÔNG"
  }).then((result) => {
    if (result.isConfirmed) {
      let token = $('meta[name="csrf-token"]').attr('content');
      let data = {
        'qrcode': qrcode,
        'event_code': eventCode,
        'custom_fields': {
          'allow_image_use': 'yes'
        },
        '_token': token,
      };
      $.ajax({
        url: '/clients/update-fields',
        type: 'POST',
        data: data,
        success: function (response) {
          if (response.status === 'success') {
            toastr.success("Xác nhận khách hàng cho phép sử dụng hình ảnh");
          }
        },
        error: function (e, textStatus, errorThrown) {
          console.error('AJAX error:', textStatus, errorThrown);

          // Handle known error scenarios
          if (e.responseJSON && e.responseJSON.message) {
            let msg = e.responseJSON.message;
            console.error(msg);

            if (isToast && msg) {
              toastr.error(msg);
            }

            return;
          }

          // Handle network errors like internet disconnected
          if (textStatus === 'error' && !e.responseJSON) {
            const networkErrorMsg = 'Không thể kết nối đến máy chủ. Kiểm tra kết nối mạng hoặc thử lại sau.';
            console.error(networkErrorMsg);
            toastr.error(networkErrorMsg);
          }
        }
      });

      return true;
    }

    toastr.success("Khách hàng xác nhận KHÔNG cho phép sử dụng hình ảnh");
    return false;
  });
}

/* galaxy-holding */
const confirmCheckinByPassDuplicate = (qrcode) => {
  Swal.fire({
    title: "Khách này đã được checkin, bạn có chắc muốn checkin lần nữa?",
    text: "Bạn sẽ không thể thay đổi sau khi thực hiện thao tác này",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "Xác nhận"
  }).then((result) => {
    if (result.isConfirmed) {
      checkinOnline(qrcode, false, 1);
      return true;
    }

    return false;
  });
}
