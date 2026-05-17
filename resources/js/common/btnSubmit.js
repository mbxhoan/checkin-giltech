export function btnSubmitLoading() {
  $('.btn-submit-form').on('click', function (e) {
    // e.preventDefault();

    // const $button = $(this);
    // const $buttonHtml = $button.html();

    // const $form = $button.closest('form');
    // const formData = new FormData($form[0]);

    // $button.prop('disabled', true).addClass('disabled')
    //        .html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading');

    // $.ajax({
    //   url: $form.attr('action'),
    //   method: $form.attr('method'),
    //   data: formData,
    //   processData: false,
    //   contentType: false,
    //   success: function (res) {
    //     // ✅ Success - reset button
    //     $button.prop('disabled', false).removeClass('disabled').html($buttonHtml);
    //     // Optionally show a message or redirect
    //   },
    //   error: function (err) {
    //     // ❌ Error - reset button
    //     $button.prop('disabled', false).removeClass('disabled').html($buttonHtml);
    //     // Optionally show error
    //   }
    // });

    e.preventDefault();
    const $button = $(this);
    const $buttonHtml = $button.html();
    const $buttonId = $button.attr('id');

    // Add loading state to the button
    $button.prop('disabled', true); // Disable the button
    $button.html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading')
    $button.addClass('disabled');

    // Allow the form to submit
    // if ($buttonId) {
    //   $(`#form-${$buttonId}`).submit();
    // } else {
    // }

    $button.closest('form').submit();

    // Reset the button state after a delay
    // setTimeout(() => {
    //   $button.prop('disabled', false); // Re-enable the button
    //   $button.html($buttonHtml); // Restore the original button text
    //   $button.removeClass('disabled');
    // }, 3000);
  });

  $('.btn-get').on('click', function (e) {
    const $button = $(this);
    let buttonHtml = $button.html();

    // Add loading state to the button
    $button.prop('disabled', true); // Disable the button
    $button.html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading')
    $button.addClass('disabled');

    toastr.info('Đang xử lý, vui lòng chờ...');

    // Reset the button state after a delay
    setTimeout(() => {
      $button.prop('disabled', false); // Re-enable the button
      $button.html(buttonHtml); // Restore the original button text
      $button.removeClass('disabled');
    }, 4500);
  });
}
