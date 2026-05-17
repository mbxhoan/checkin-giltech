
import { handleChangeStatus } from "./_handleChangeStatus";
import { renderSendMailTable } from "./_renderSendMailTable";

$(document).ready(function () {
  handleChangeStatus();
  renderSendMailTable('#history-send-mail');
});
