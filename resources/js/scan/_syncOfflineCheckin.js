
import { renderCheckinOfflineTable } from "./_renderCheckinOfflineTable";

export const handleClickSyncOfflineCheckin = () => {
  $("#btn-sync-offline-checkins").on("click", function (e) {
    e.preventDefault();
    syncMultiCheckin($(this));
  });
}

const syncMultiCheckin = (btn) => {
  const eventCode = $("#event_code").val();
  const datas = JSON.parse(localStorage.getItem(`checkins`));
  const btnHtml = btn.html();
  let token = $('meta[name=csrf-token]').prop('content');

  if (datas === null || datas === undefined) {
    console.log("No data found in cache");
    toastr.error("No data found in cache");
    return false;
  }

  let data = {
    'event_code': eventCode,
    'total_records': datas.length,
    'data': datas,
    '_token': token
  };

  $.ajax({
    method: "POST",
    url: '/sync-offline',
    contentType: "application/json",
    data: JSON.stringify(data),
    dataType: "json",
    beforeSend: function () {
      // $('.overlay-layer').show();
      btn.prop('disabled', true).addClass('disabled').html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading');
    },
  })
    .fail(function (e) {
      btn.prop('disabled', false).removeClass('disabled').html(btnHtml);

      if (e.responseJSON !== undefined && e.responseJSON !== null) {
        if (e.responseJSON.message !== undefined && e.responseJSON.message !== null) {
          toastr.error(e.responseJSON.message);
          return;
        }
      }

      toastr.error('Đã xảy ra lỗi đồng bộ hoặc Không thể kết nối đến máy chủ');
    })
    .done(function (response) {
      if (response.status === 'success') {
        if (response.message != '') {
          toastr.success(response.message);
        }

        btn.prop('disabled', false).removeClass('disabled').html(btnHtml);
        /* clear cache offline data */
        clearCacheCheckins(eventCode);
        renderCheckinOfflineTable();
      }
    });

  // $('.overlay-layer').hide();
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
