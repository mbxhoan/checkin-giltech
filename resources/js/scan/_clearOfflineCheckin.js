"use-strict";

import { renderCheckinOfflineTable } from "./_renderCheckinOfflineTable";

export const handleClickClearOfflineCheckin = () => {
  $("#btn-clear-offline-checkins").on("click", function (e) {
    e.preventDefault();
    if (confirm('Bạn có chắc muốn reset dữ liệu checkin offline tại máy?')) {
      clearOfflienCheckins($(this));
      console.log('Confirmed!');
    } else {

    }
  });
}

const clearOfflienCheckins = (btn) => {
  const eventCode = $("#event_code").val();
  const datas = JSON.parse(localStorage.getItem(`checkins`));
  const btnHtml = btn.html();
  btn.prop('disabled', true).addClass('disabled').html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading');

  if (datas === null || datas === undefined) {
    console.log("No data found in cache");
    toastr.error("No data found in cache");
    return false;
  }

  clearCacheCheckins(eventCode);
  renderCheckinOfflineTable();
  btn.prop('disabled', false).removeClass('disabled').html(btnHtml);
}

const clearCacheCheckins = (eventCode) => {
  if (localStorage.getItem(`checkins`)) {
    localStorage.removeItem(`checkins`);
    toastr.success("Đã xoá dữ liệu checkin tại máy");
    console.log("Checkins cache cleared and deleted.");
  } else {
    console.log("No checkins cache found.");
  }
}
