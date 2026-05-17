
import { handleDraggableCheckin } from "./_handleDraggableCheckin";

export const renderBackground = () => {
  $.ajax({
    url: $('#backgroundContainer input#url').val(),
    type: 'GET',
    success: function (response) {
      // Handle the successful response from the server

      if (response.status === 'success') {
        // console.log(response.message);

        // if (isToast) {
        //   toastr.success(response.message);
        // }

        $('#backgroundContainer').html(response.data.html);

        setTimeout(() => {
          handleDraggableCheckin();
        }, 250);
      }
    },
    error: function (e) {
      // Handle errors during the AJAX request
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
}
