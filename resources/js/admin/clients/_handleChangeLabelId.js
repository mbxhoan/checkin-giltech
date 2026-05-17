"use-strict";

export const handleChangeLabelId = (modalId, eleId, targetEleId) => {
  $(eleId).on('change', function (event) {
    let value = $(this).val();
    let clientId = $(`${modalId} #client_id`).val();
    console.log(clientId);

    // let url = new URL($(this).data('url'), window.location.origin);
    // const params = new URLSearchParams(url.search);
    // params.set('label_id', value);
    // url.search = params.toString();
    // window.location.href = url.toString();

    $.ajax({
      url: `/admin/labels/render-label/${value}?client_id=${clientId}`,
      type: 'GET',
      success: function (response) {
        console.log(response);

        if (response.status === 'success') {
          $(targetEleId).html(response.data.html);
        }
      },
      error: function (e) {
        if (e.responseJSON.message) {
          if (isToast) {
            toastr.error(e.responseJSON.message);
          }

          console.error(e.responseJSON.message);
          return;
        }

        console.error(e);
      }
    });
  });
}
