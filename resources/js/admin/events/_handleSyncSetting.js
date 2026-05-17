
export const handleSyncSetting = (isToast = true) => {
  $('#btn-sync-settings').on('click', function (e) {
    e.preventDefault(); // Prevent default link behavior
    let url = $(this).data('url');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.post(url, {
      _token: csrfToken // Include CSRF token
    })
      .done(function (response) {
        if (response.status === 'success') {
          console.log(response.message);
          toastr.success(response.message);
          $('#settings').html(response.data.html);
        }
      })
      .fail(function (xhr) {
        if (xhr.responseJSON.message) {
          if (isToast) {
            toastr.error(e.responseJSON.message);
          }

          console.error(e.responseJSON.message);
          return;
        }

        console.error(xhr.responseText);
      });
  });
}
