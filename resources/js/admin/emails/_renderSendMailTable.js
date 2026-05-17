"use-strict";

import { handleChangeStatus } from "./_handleChangeStatus";

export const renderSendMailTable = (tableId) => {
  const $table = $(tableId);

  if (!$table.length) {
    console.warn(`Element ${tableId} not found.`);
    return;
  }

  const time = $table.data('time') || 5; // default to 5 seconds if not set
  
  setInterval(() => {
    getHtml(tableId);
  }, time*1000);
}

const getHtml = (tableId) => {
  $.ajax({
    url: $(tableId).data('url'),
    type: 'GET',
    success: function (response) {
      if (response.status === 'success') {
        console.log(response);
        let ele = $('#progress-bar').data('ele');
        let html1 = response.data.html1;
        let html2 = response.data.html2;
        $(tableId).html(html2);

        if (ele) {
          $(ele).html(html1);
        }

        handleChangeStatus();
        return
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
