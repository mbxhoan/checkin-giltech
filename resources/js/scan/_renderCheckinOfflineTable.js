"use-strict";

export const renderCheckinOfflineTable = () => {
  const eventCode = $("#event_code").val();
  const checkins = JSON.parse(localStorage.getItem(`checkins`));
  const tbody = $("#checkins-table tbody");
  tbody.empty(); // Clear previous rows

  if (checkins === null || checkins === undefined) {
    $("#checkins_count").text(0);
  } else {
    if (checkins.length > 0) {
      checkins.forEach((checkin, index) => {
        const row = `
          <tr>
            <td>${index + 1}</td>
            <td>${checkin.qrcode}</td>
            <td>${checkin.scan_time}</td>
          </tr>
        `;

        tbody.append(row);
      });
      /* đếm số lượng */
      $("#checkins_count").text(checkins.length);
    }
  }
}
