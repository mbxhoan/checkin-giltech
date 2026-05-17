
import { rendeBarChart } from "../reports/_rendeBarChart";
import { renderPieChart } from "../reports/_renderPieChart";
import { renderTicketAnalytics } from "../videc/_renderTicketAnalytics";

$(document).ready(function () {
  rendeBarChart();
  renderPieChart();
  renderTicketAnalytics();
});
