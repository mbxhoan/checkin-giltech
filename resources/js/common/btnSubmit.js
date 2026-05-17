export function btnSubmitLoading() {
  $('.btn-submit-form').on('click', function (e) {
    e.preventDefault();
    const $button = $(this);
    const $form = $button.closest('form');
    const loadingText = $button.data('loadingText') || 'Đang xử lý...';

    // Add loading state to the button
    $button.prop('disabled', true);
    $button.html(`<i class="fa-solid fa-spinner fa-spin-pulse"></i> ${loadingText}`)
    $button.addClass('disabled');

    window.GiltechLoadingOverlay?.show($button.data('loadingMessage') || 'Đang gửi dữ liệu và đồng bộ trạng thái...');

    $form.trigger('submit');
  });

  $('.btn-get').on('click', function (e) {
    const $button = $(this);
    let buttonHtml = $button.html();

    // Add loading state to the button
    $button.prop('disabled', true);
    $button.html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Đang tải...')
    $button.addClass('disabled');

    window.GiltechLoadingOverlay?.show($button.data('loadingMessage') || 'Đang tải dữ liệu...');
    toastr.info('Đang xử lý, vui lòng chờ...');

    // Reset the button state after a delay
    setTimeout(() => {
      $button.prop('disabled', false);
      $button.html(buttonHtml);
      $button.removeClass('disabled');
      window.GiltechLoadingOverlay?.hide();
    }, 4500);
  });
}
