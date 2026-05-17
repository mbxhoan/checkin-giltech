"use-strict";

export const submitFormOnChange = (inputId, isToast = false, method = 'POST') => {
  const formIdSelector = `#${inputId}`;
  const $form = $(formIdSelector);
  const formData = $form.serialize(); // Serialize the form data
  const formAction = $form.attr('action'); // Get the form's action URL
  const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token

  $.ajax({
    url: formAction,
    type: method,
    data: formData + '&_token=' + csrfToken, // Include form data and CSRF token
    success: function (response) {
      // Handle the successful response from the server

      if (response.status === 'success') {
        console.log(response.message);

        if (isToast) {
          toastr.success(response.message);
        }
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
}
