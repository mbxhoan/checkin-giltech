
import { loading } from "./_loading";
import { printOffline } from "./_printOffline";
import { renderCheckinOfflineTable } from "./_renderCheckinOfflineTable";
import { renderError } from "./_renderError";
import { renderSuccess } from "./_renderSuccess";

export const checkinOffline = (qrcode) => {
  console.log("OFFLINE MODE | Scanned code:", qrcode);
  loading();

  const eventCode = $("#event_code").val();
  const clients = JSON.parse(localStorage.getItem(`clients`));
  const client = clients.find(client => client.qrcode == qrcode);

  if (client === null || client === undefined) {
    console.log("Client is null or undefined");
    renderError(null, "KHÔNG TÌM THẤY KHÁCH MỜI");
    return false;
  } else {
    if (client.custom_fields) {
      $.extend(client, client.custom_fields);
      delete client.custom_fields;
    }
  }

  let checkins = JSON.parse(localStorage.getItem(`checkins`)) || [];
  // let scanTime = new Date().toISOString().replace('T', ' ').substr(0, 19);
  let scanTime = getCurrentDateTime();

  const newCheckin = {
    event_code: eventCode,
    qrcode: qrcode,
    scan_time: scanTime // Format Y-m-d H:i:s
  };

  checkins.push(newCheckin);
  localStorage.setItem(`checkins`, JSON.stringify(checkins));
  renderSuccess(client, "CHECKED IN", '#msg-success', false);
  renderCheckinOfflineTable();

  /* print */
  const label = $(`#ms-label-${client.id}`);

  if (label.length) {
    printOffline(`ms-label-${client.id}`, true);
  } else{
    console.warn(`Label print #${client.id} not found.`);
  }

  return true;
};

const getCurrentDateTime = () => {
  const pad = (n) => n.toString().padStart(2, '0');
  const now = new Date();
  const scanTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ` +
                  `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
  return scanTime;
}


