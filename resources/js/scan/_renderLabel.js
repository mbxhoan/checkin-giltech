"use-strict";

import { print } from "./_print";

export const renderLabel = (clientId) => {
  let labelId = $('#label_id').val();

  if (labelId) {
    console.log(`Label ID: ${labelId}`);
    console.log(`Client ID: ${clientId}`);
    $.ajax({
      url: `/render-label/${labelId}?client_id=${clientId}`,
      type: 'GET',
      success: function (response) {
        if (response.status === 'success') {
          console.log("Rendering label...");
          $('#printContainer').html(response.data.html);
          console.log("Rendered!");
          print(true);
        }
      },
      error: function (e) {
        // Handle errors during the AJAX request
        if (e.responseJSON.message) {
          console.error(e.responseJSON.message);
          return;
        }

        console.error(e);
      }
    });
  }
}
