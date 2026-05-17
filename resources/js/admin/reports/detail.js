"use-strict";

import { rendeBarChart } from "./_rendeBarChart";
import { renderPieChart } from "./_renderPieChart";
import { renderTicketAnalytics } from "../videc/_renderTicketAnalytics";

$(document).ready(function () {
  rendeBarChart();
  renderPieChart();
  renderTicketAnalytics();

  // Call immediately on page load
  // fetchReportData();
  // Repeat every 1 minute (60000 milliseconds)
  setInterval(fetchReportData, 603000);
});

const fetchReportData = () => {
  const eventId = $('#event_id').val();
  $.ajax({
    url: `/admin/reports/render-report/${eventId}`,
    type: 'GET',
    success: function (response) {
      console.log(response);
      $('#report').html(response.data.html);
      $('#email').html(response.data.html2);
      renderPieChart();
      rendeBarChart();
      renderTicketAnalytics();

      // if (response.status === 'success') {
      // }
    },
    error: function (e) {
      if (e.responseJSON.message) {
        toastr.error(e.responseJSON.message);
        console.error(e.responseJSON.message);
        return;
      }

      console.error(e);
    }
  });
}
