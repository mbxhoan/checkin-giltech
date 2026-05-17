
import { submitFormOnChange } from "../common/submitFormOnChange";

export const handleEditChangeField = (isToast = false, fns = []) => {
  $('.edit-change-field').on('change', function (e) {
    changeField($(this), isToast, fns);
  });

  // $('.edit-change-field').on('keyup', function (e) {
  //   changeField($(this), isToast, fns);
  // })
}

const changeField = ($this, isToast, fns) => {
    const inputType = $this.prop('type');
    let id = $this.attr('id');
    let value;

    if (inputType === 'checkbox') {
      if (!($this.hasClass('checkbox-value'))) {
        value = $this.prop('checked');
        $this.val(value);
      }
    } else {
      value = $this.val();
    }

    updateCheckboxValues();
    submitFormOnChange(id);

    $this.addClass("border-success text-success");

    setTimeout(() => {
      fns.forEach(fn => {
        if (typeof fn === 'function') {
          fn();
        }
      });
    }, 300);
}

const updateCheckboxValues = () => {
  $('.edit-change-field').each(function () {
    const $this = $(this);
    const inputType = $this.prop('type');

    if (inputType === 'checkbox') {
      /* checkbox-value đặc biệt thì sẽ không chuyển về 1 hoặc 0 mà giữ nguyên giá trị */
      if (!$this.hasClass('checkbox-value')) {
        if ($this.prop('checked')) {
          $this.val(1); // Set value to 1 if checked
        } else {
          $this.val(0); // Set value to 1 if checked
        }
      }
    }
  });
};

/* const submitForm = (inputId) => {
  const formIdSelector = `#${inputId}`;
  const $form = $(formIdSelector);
  const formData = $form.serialize(); // Serialize the form data
  const formAction = $form.attr('action'); // Get the form's action URL
  const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token

  $.ajax({
    url: formAction,
    type: 'POST',
    data: formData + '&_token=' + csrfToken, // Include form data and CSRF token
    success: function (response) {
      // Handle the successful response from the server

      if (response.status === 'success') {
        console.log(response.message);
      }
    },
    error: function (e) {
      // Handle errors during the AJAX request
      if (e.responseJSON.message) {
        toastr.error(e.responseJSON.message);
        return;
      }

      console.error(e);
      // You might want to display an error message to the user
    }
  });
} */

/* const submitForm = (inputId, inputType, inputValue) => {
  const formIdSelector = `#${inputId}`;
  const $form = $(formIdSelector);
  const $inputInForm = $form.find(`#${inputId}`);

  // Update the value of the checkbox input within the form if it's a checkbox
  if (inputType === 'checkbox') {
    $inputInForm.val(inputValue); // Set the value to true or false
  }

  // Perform the AJAX submit directly here
  const formData = $form.serialize(); // Serialize the form data
  const formAction = $form.attr('action'); // Get the form's action URL
  const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token

  $.ajax({
    url: formAction,
    type: 'POST',
    data: formData + '&_token=' + csrfToken, // Include form data and CSRF token
    success: function (response) {
      // Handle the successful response from the server
      console.log('Form submitted successfully!', response);
      // You might want to display a success message to the user
    },
    error: function (xhr, status, error) {
      // Handle errors during the AJAX request
      console.error('Error submitting form:', error, xhr);
      // You might want to display an error message to the user
    }
  });
} */
