"use-strict";

import { handleClickPrint } from "../labels/_print";
import { handleFillQrcode } from "./_handle-fill-qrcode";
import { handleChangeLabelId } from "./_handleChangeLabelId";
import { handleClickPrintByClass } from "./_handleClickPrintByClass";
import { saveFormPrint } from "./_print";

$(document).ready(function () {
  handleFillQrcode();
  handleChangeLabelId('.modal', '#label_id', '#printContainer');
  handleClickPrintByClass();

  savePrint();
});

function savePrint() {
  $('#savePrintBtn').on('click', function (e) {
    e.preventDefault();
    const form = $('#save-form');
    console.log(form.serialize());

    $.ajax({
      url: '/admin/clients/save-print',
      method: 'POST',
      data: form.serialize(),
      headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()
      },
      success: function (response) {
        console.log(response.data.html);
        $('#print-block').html(response.data.html);
        saveFormPrint(true, response.data.redirectTo);
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
  });
}
