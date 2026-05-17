"use-strict";

import { renderLabel } from "../label_details/_renderLabel";

export const handleUpdateLabel = (isToast = false, fns = []) => {
  $('.edit-update-label').on('change', function (e) {
    const $this = $(this);
    const inputType = $this.prop('type');
    let id = 'formUpdateLabel';
    let value;

    if (inputType === 'checkbox') {
      value = $this.prop('checked');
      $this.val(value);
    } else {
      value = $this.val();
    }

    // submitFormOnChange(id, false, 'PUT');
    const formIdSelector = `#formUpdateLabel`;
    const $form = $(formIdSelector);

    let formData = $form.serialize(); // string
    let formObj = Object.fromEntries(new URLSearchParams(formData)); // convert to object
    formObj._method = 'POST'; // edit
    formData = new URLSearchParams(formObj).toString();

    // const formData = $form.serialize(); // Serialize the form data
    const formAction = $("#url").val(); // Get the form's action URL
    const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token
    console.log(formData);
    console.log(formAction);

    $.ajax({
      url: formAction,
      type: "POST",
      data: {
        'name': $('#name').val(),
        'width': $('#width').val(),
        'height': $('#height').val(),
        'unit': $('#unit').val(),
        'type': $('#type').val(),
        '_token': csrfToken,
      },
      success: function (response) {
        if (response.status === 'success') {
          console.log(response.message);

          if (isToast) {
            toastr.success(response.message);
          }

          renderLabel();
        }
      },
      error: function (e) {
        // Handle errors during the AJAX request
        if (e.responseJSON.message) {
          if (isToast) {

          }

          toastr.error(e.responseJSON.message);
          console.error(e.responseJSON.message);
          return;
        }

        console.error(e);
      }
    });
    $this.addClass("border-success text-success");
  })
}
